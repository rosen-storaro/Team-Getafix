using JobTracking.App.Contracts.Base;
using System.Security.Cryptography;
using System.Text;
using JobTracking.App.Contracts;
using JobTracking.DataAccess;
using JobTracking.DataAccess.Data.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;

namespace JobTracking.App.Implementation;


public class AuthService : IAuthService
    {
        private readonly ApplicationDbContext _context;
        private readonly IConfiguration _configuration;

        public AuthService(ApplicationDbContext context, IConfiguration configuration)
        {
            _context = context;
            _configuration = configuration;
        }

        public async Task<User> AuthenticateUser(string usernameOrEmail, string password)
        {
            // FIX: Use ToLower() for case-insensitive comparison in the query
            var lowerCaseUsernameOrEmail = usernameOrEmail.ToLower();
            var user = await _context.Users
                           .FirstOrDefaultAsync(u => u.Username.ToLower() == lowerCaseUsernameOrEmail ||
                                                     u.Email.ToLower() == lowerCaseUsernameOrEmail);

            if (user == null)
            {
                return null;
            }

            string hashedProvidedPassword = HashPassword(password);

            if (!VerifyPassword(hashedProvidedPassword, user.PasswordHash))
            {
                return null;
            }

            return user;
        }

        public string HashPassword(string password)
        {
            using (SHA256 sha256Hash = SHA256.Create())
            {
                byte[] bytes = sha256Hash.ComputeHash(Encoding.UTF8.GetBytes(password));
                StringBuilder builder = new StringBuilder();
                for (int i = 0; i < bytes.Length; i++)
                {
                    builder.Append(bytes[i].ToString("x2"));
                }
                return builder.ToString();
            }
        }

        public bool VerifyPassword(string computedHash, string storedHash)
        {
            return computedHash.Equals(storedHash, StringComparison.OrdinalIgnoreCase);
        }

        public bool CheckUserNameAvailability(string username)
        {
            return _context.Users.Any(u => u.Username.ToLower() == username.ToLower());
        }

        public bool CheckEmailAvailability(string email)
        {
            return _context.Users.Any(u => u.Email.ToLower() == email.ToLower());
        }
    }