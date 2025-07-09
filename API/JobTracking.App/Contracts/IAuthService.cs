using JobTracking.DataAccess.Data.Models;

namespace JobTracking.App.Contracts;

public interface IAuthService
{
    Task<User> AuthenticateUser(string usernameOrEmail, string password);
    string HashPassword(string password);
    bool VerifyPassword(string computedHash, string storedHash);
    bool CheckUserNameAvailability(string username);
    bool CheckEmailAvailability(string email);
    
}