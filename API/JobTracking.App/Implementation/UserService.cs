using JobTracking.App.Contracts.Base;
using JobTracking.DataAccess;
using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.DTOs.Response;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.SqlServer;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using JobTracking.App.Contracts;
using Microsoft.EntityFrameworkCore;
using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.Enums;
using JobTracking.Domain.Filters;

namespace JobTracking.App.Implementation;

public class UserService : IUserService
    {
        private readonly ApplicationDbContext _context;
        private readonly IAuthService _authService;

        public UserService(ApplicationDbContext context, IAuthService authService)
        {
            _context = context;
            _authService = authService;
        }

        public async Task<IEnumerable<User>> GetAllUsersAsync(UserFilter filter = null)
        {
            IQueryable<User> users = _context.Users;

            if (filter != null)
            {
                if (!string.IsNullOrEmpty(filter.Username))
                {
                    // FIX: Ensure both sides are ToLower() for Contains
                    users = users.Where(u => u.Username.ToLower().Contains(filter.Username.ToLower()));
                }
                if (!string.IsNullOrEmpty(filter.Email))
                {
                    // FIX: Ensure both sides are ToLower() for Contains
                    users = users.Where(u => u.Email.ToLower().Contains(filter.Email.ToLower()));
                }
                if (!string.IsNullOrEmpty(filter.FirstName))
                {
                    // FIX: Ensure both sides are ToLower() for Contains
                    users = users.Where(u => u.FirstName.ToLower().Contains(filter.FirstName.ToLower()));
                }
                if (!string.IsNullOrEmpty(filter.LastName))
                {
                    // FIX: Ensure both sides are ToLower() for Contains
                    users = users.Where(u => u.LastName.ToLower().Contains(filter.LastName.ToLower()));
                }
                if (!string.IsNullOrEmpty(filter.Role))
                {
                    // This part is fine as Enum.TryParse handles string comparison and then it's enum equality.
                    if (Enum.TryParse(filter.Role, true, out UserRole roleEnum))
                    {
                        users = users.Where(u => u.Role == roleEnum);
                    }
                }
            }

            return await users.ToListAsync();
        }

        public async Task<UserResponseDto> GetUserByIdAsync(int id)
        {
            var user = await _context.Users.FindAsync(id);
            if (user == null) return null;

            return new UserResponseDto()
            {
                Id = user.Id,
                Address = user.Address,
                FirstName = user.FirstName,
                LastName = user.LastName,
                MiddleName = user.MiddleName,
                Username = user.Username,
                Email = user.Email,
                Role = user.Role
            };
        }

        public async Task<User> GetUserByUsernameOrEmailAsync(string usernameOrEmail)
        {
            var lowerCaseUsernameOrEmail = usernameOrEmail.ToLower();
            return await _context.Users
                .FirstOrDefaultAsync(u =>
                    u.Username.ToLower() == lowerCaseUsernameOrEmail ||
                    u.Email.ToLower() == lowerCaseUsernameOrEmail);
        }

        public async Task<User> AddUserAsync(User user)
        {
            var lowerCaseUsername = user.Username.ToLower();
            var lowerCaseEmail = user.Email.ToLower();

            if (await _context.Users.AnyAsync(u =>
                u.Username.ToLower() == lowerCaseUsername ||
                u.Email.ToLower() == lowerCaseEmail))
            {
                throw new InvalidOperationException("Username or email already exists.");
            }

            if (string.IsNullOrEmpty(user.PasswordHash))
            {
                 throw new ArgumentException("Password cannot be empty.");
            }
            user.PasswordHash = _authService.HashPassword(user.PasswordHash);

            _context.Users.Add(user);
            await _context.SaveChangesAsync();
            return user;
        }

        public async Task<User> UpdateUserAsync(User user)
        {
            var existingUser = await _context.Users.FindAsync(user.Id);
            if (existingUser == null)
            {
                return null;
            }

            var lowerCaseNewUsername = user.Username.ToLower();
            var lowerCaseNewEmail = user.Email.ToLower();

            if ((!string.IsNullOrEmpty(user.Username) && existingUser.Username.ToLower() != lowerCaseNewUsername &&
                 await _context.Users.AnyAsync(u => u.Username.ToLower() == lowerCaseNewUsername && u.Id != user.Id)) ||
                (!string.IsNullOrEmpty(user.Email) && existingUser.Email.ToLower() != lowerCaseNewEmail &&
                 await _context.Users.AnyAsync(u => u.Email.ToLower() == lowerCaseNewEmail && u.Id != user.Id)))
            {
                throw new InvalidOperationException("Updated username or email already exists for another user.");
            }

            existingUser.FirstName = user.FirstName;
            existingUser.MiddleName = user.MiddleName;
            existingUser.LastName = user.LastName;
            existingUser.Username = user.Username;
            existingUser.Email = user.Email;
            existingUser.Role = user.Role;
            existingUser.Address = user.Address;

            _context.Entry(existingUser).State = EntityState.Modified;
            try
            {
                await _context.SaveChangesAsync();
            }
            catch (DbUpdateConcurrencyException)
            {
                if (!await UserExistsAsync(user.Id))
                {
                    return null;
                }
                else
                {
                    throw;
                }
            }
            return existingUser;
        }

        public async Task<bool> DeleteUserAsync(int id)
        {
            var userToRemove = await _context.Users.FindAsync(id);
            if (userToRemove == null)
            {
                return false;
            }

            _context.Users.Remove(userToRemove);
            await _context.SaveChangesAsync();
            return true;
        }

        public async Task<bool> UserExistsAsync(int id)
        {
            return await _context.Users.AnyAsync(e => e.Id == id);
        }

        public async Task<bool> UpdateUserPasswordAsync(int userId, string newHashedPassword)
        {
            var user = await _context.Users.FindAsync(userId);
            if (user == null)
            {
                return false;
            }
            user.PasswordHash = newHashedPassword;
            _context.Entry(user).State = EntityState.Modified;
            await _context.SaveChangesAsync();
            return true;
        }
    }