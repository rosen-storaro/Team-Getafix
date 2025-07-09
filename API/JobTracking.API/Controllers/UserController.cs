using System.Security.Claims;
using JobTracking.App.Contracts;
using JobTracking.App.Implementation;
using JobTracking.DataAccess.Data.Models;
using JobTracking.Domain.DTOs.Request;
using JobTracking.Domain.DTOs.Response;
using JobTracking.Domain.Enums;
using JobTracking.Domain.Filters;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http.HttpResults;
using Microsoft.AspNetCore.Mvc;

namespace JobTracking.API.Controllers;

[ApiController]
[Route("api/[controller]")]
public class UsersController : ControllerBase
{
    private readonly IUserService _userService;
    private readonly IAuthService _authService;

    public UsersController(IUserService userService, IAuthService authService)
    {
        _userService = userService;
        _authService = authService;
    }

    [HttpPost("login")]
    public async Task<ActionResult<LoginResponseDto>> Login([FromBody] LoginRequestDto request)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        var user = await _authService.AuthenticateUser(request.UsernameOrEmail, request.Password);

        if (user == null)
        {
            return Unauthorized("Invalid username/email or password.");
        }

        // No token generation here
        return Ok(new LoginResponseDto
        {
            UserId = user.Id,
            Username = user.Username,
            Email = user.Email,
            Role = user.Role.ToString()
        });
    }

    [HttpGet]
    [Authorize(Roles = "Admin,User")]
    public async Task<ActionResult<IEnumerable<UserResponseDto>>> GetUsers([FromQuery] UserFilter filter)
    {
        var users = await _userService.GetAllUsersAsync(filter);
        if (users == null || !users.Any())
        {
            return NotFound("No users found matching the criteria.");
        }

        var userDtos = users.Select(u => new UserResponseDto
        {
            Id = u.Id,
            FirstName = u.FirstName,
            MiddleName = u.MiddleName,
            LastName = u.LastName,
            Username = u.Username,
            Email = u.Email,
            Role = u.Role,
            Address = u.Address
        }).ToList();

        return Ok(userDtos);
    }

    [HttpGet("{id}")]
    [Authorize(Roles = "Admin,User")]
    public async Task<ActionResult<UserResponseDto>> GetUser(int id)
    {
        var userDto = await _userService.GetUserByIdAsync(id);

        if (userDto == null)
        {
            return NotFound("User not found.");
        }
        
        if (User.IsInRole(UserRole.User.ToString()) && userDto.Id != GetCurrentUserId())
        {
            return Forbid("You do not have permission to view this user's profile.");
        }

        return Ok(userDto);
    }

    [HttpPost]
    [AllowAnonymous]
    public async Task<ActionResult<UserResponseDto>> CreateUser([FromBody] UserCreateRequestDto request)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        var newUser = new User
        {
            FirstName = request.FirstName,
            MiddleName = request.MiddleName,
            LastName = request.LastName,
            Username = request.Username,
            Email = request.Email,
            PasswordHash = request.Password,
            Role = request.Role,
            Address = request.Address
        };

        try
        {
            var addedUser = await _userService.AddUserAsync(newUser);

            var userResponse = new UserResponseDto
            {
                Id = addedUser.Id,
                FirstName = addedUser.FirstName,
                MiddleName = addedUser.MiddleName,
                LastName = addedUser.LastName,
                Username = addedUser.Username,
                Email = addedUser.Email,
                Role = addedUser.Role,
                Address = addedUser.Address
            };

            return CreatedAtAction(nameof(GetUser), new { id = userResponse.Id }, userResponse);
        }
        catch (InvalidOperationException ex)
        {
            return Conflict(ex.Message);
        }
        catch (ArgumentException ex)
        {
            return BadRequest(ex.Message);
        }
    }

    [HttpPut("{id}")]
    [Authorize(Roles = "Admin,User")]
    public async Task<IActionResult> UpdateUser(int id, [FromBody] UserUpdateRequestDto request)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ModelState);
        }

        // The User.IsInRole check relies on authentication context from a scheme
        if (User.IsInRole(UserRole.User.ToString()) && id != GetCurrentUserId())
        {
            return Forbid("You do not have permission to update this user's profile.");
        }

        var existingUserDto = await _userService.GetUserByIdAsync(id);
        if (existingUserDto == null)
        {
            return NotFound("User not found.");
        }

        var userToUpdate = await _userService.GetUserByUsernameOrEmailAsync(existingUserDto.Username);
        if (userToUpdate == null) { return NotFound("User not found for update."); }

        userToUpdate.FirstName = request.FirstName;
        userToUpdate.MiddleName = request.MiddleName;
        userToUpdate.LastName = request.LastName;
        userToUpdate.Username = request.Username;
        userToUpdate.Email = request.Email;
        userToUpdate.Role = request.Role;
        userToUpdate.Address = request.Address;

        try
        {
            var updatedUser = await _userService.UpdateUserAsync(userToUpdate);

            if (updatedUser == null)
            {
                return NotFound("User not found during update.");
            }

            return NoContent();
        }
        catch (InvalidOperationException ex)
        {
            return Conflict(ex.Message);
        }
    }

    [HttpDelete("{id}")]
    [Authorize(Roles = "Admin")]
    public async Task<IActionResult> DeleteUser(int id)
    {
        var userExists = await _userService.UserExistsAsync(id);
        if (!userExists)
        {
            return NotFound("User not found.");
        }
        
        if (GetCurrentUserId() == id && User.IsInRole(UserRole.Admin.ToString()))
        {
            return BadRequest("Admins cannot delete their own account through this endpoint.");
        }

        var result = await _userService.DeleteUserAsync(id);
        if (!result)
        {
            return StatusCode(500, "Failed to delete user.");
        }

        return NoContent();
    }

    private int GetCurrentUserId()
    {
        var userIdClaim = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
        if (userIdClaim != null && int.TryParse(userIdClaim, out int userId))
        {
            return userId;
        }
        throw new InvalidOperationException("User ID claim not found or invalid. Authentication context is missing.");
    }
}