using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Filters;

namespace JobTracking.App.Contracts;

public interface IUserService
{
    Task<IEnumerable<User>> GetAllUsersAsync(UserFilter filter = null);
    Task<UserResponseDto> GetUserByIdAsync(int id); // Updated to UserResponseDto
    Task<User> GetUserByUsernameOrEmailAsync(string usernameOrEmail);
    Task<User> AddUserAsync(User user);
    Task<User> UpdateUserAsync(User user);
    Task<bool> DeleteUserAsync(int id);
    Task<bool> UserExistsAsync(int id);
    Task<bool> UpdateUserPasswordAsync(int userId, string newHashedPassword);
}