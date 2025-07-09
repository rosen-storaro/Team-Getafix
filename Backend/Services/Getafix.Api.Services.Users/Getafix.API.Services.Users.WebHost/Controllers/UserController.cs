using Getafix.Api.EventBus.Shared.Services.Contracts;
using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Shared.Models;
using Getafix.Api.Services.Users.Shared.Models.Password;
using Getafix.Api.Services.Users.Shared.Models.User;
using Getafix.Api.Services.Shared.Data.Models.Identity;
using AutoMapper;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace Getafix.Api.Services.Users.WebHost.Controllers;

/// <summary>
/// Controller for admin operations.
/// </summary>
[Route("user")]
[ApiController]
public class UserController : ControllerBase
{
    private readonly IUserService userService;
    private readonly IAuthService authService;
    private readonly ICurrentUser currentUser;
    private readonly IMapper mapper;
    private readonly ILogger<UserController> logger;
    private readonly IIntegrationEventService eventService;

    /// <summary>
    /// Initializes a new instance of the <see cref="UserController"/> class.
    /// </summary>
    /// <param name="userService">User Service.</param>
    /// <param name="authService">Auth Service.</param>
    /// <param name="currentUser">Current User.</param>
    /// <param name="mapper">Mapper.</param>
    /// <param name="logger">Logger.</param>
    /// <param name="eventService">Event service.</param>
    public UserController(
        IUserService userService,
        IAuthService authService,
        ICurrentUser currentUser,
        IMapper mapper,
        ILogger<UserController> logger,
        IIntegrationEventService eventService)
    {
        this.userService = userService;
        this.authService = authService;
        this.currentUser = currentUser;
        this.mapper = mapper;
        this.logger = logger;
        this.eventService = eventService;
    }

    /// <summary>
    /// Gets info of the current admin.
    /// </summary>
    /// <returns>Response with the result.</returns>
    [HttpGet("current")]
    [Authorize]
    public async Task<ActionResult<UserVM>> GetCurrentUserAsync()
    {
        var result = await this.userService.GetUserByIdAsync(this.currentUser.UserId);

        if (result is null)
        {
            return this.NotFound();
        }

        return this.Ok(result);
    }

    /// <summary>
    /// Changes the password of the current user.
    /// </summary>
    /// <param name="updatePasswordModel">Update password model.</param>
    /// <returns>Response with the result.</returns>
    [HttpPost("change-password")]
    [Authorize(Policy = UserPolicies.AdminPermissions)]
    public async Task<IActionResult> ChangerUserPasswordAsync([FromBody] UpdatePasswordModel updatePasswordModel)
    {
        this.logger.LogInformation("Changing password for admin with id {UserId}", this.currentUser.UserId);

        var user = await this.userService.GetUserByIdAsync(this.currentUser.UserId);

        if (!await this.authService.CheckIsPasswordCorrectAsync(user!.UserName, updatePasswordModel.OldPassword))
        {
            this.logger.LogWarning("Changing password for admin with id {UserId} failed. Old password is incorrect.", this.currentUser.UserId);

            return this.BadRequest(new ResponseModel
            {
                Status = "password-change-failed",
                Message = "Old password is incorrect.",
            });
        }

        var result = await this.userService.ChangePasswordAsync(this.currentUser.UserId, updatePasswordModel.NewPassword);

        if (!result.Succeeded)
        {
            this.logger.LogWarning("Changing password for admin with id {UserId} failed. {Errors}", this.currentUser.UserId, result.Errors.First().Description);

            return this.BadRequest(new ResponseModel
            {
                Status = "password-change-failed",
                Message = result.Errors.FirstOrDefault()?.Description,
            });
        }

        this.logger.LogInformation("Changing password for admin with id {UserId} succeeded.", this.currentUser.UserId);

        return this.Ok(new ResponseModel
        {
            Status = "password-changed-success",
            Message = "Password change successful.",
        });
    }

    /// <summary>
    /// Updates user with id.
    /// </summary>
    /// <param name="id">Id of the user.</param>
    /// <param name="model">User Update Model.</param>
    /// <returns>Response with the result.</returns>
    [HttpPut("{id}")]
    [Authorize(Policy = UserPolicies.AdminPermissions)]
    public async Task<IActionResult> UpdateUserAsync(string id, [FromBody] UserUM model)
    {
        this.logger.LogInformation("Updating user with id {UserId}", id);

        var user = await this.userService.GetUserByIdAsync(id);

        if (user is null)
        {
            this.logger.LogWarning("Updating user with id {UserId} failed. User not found.", id);

            return this.NotFound(new ResponseModel
            {
                Status = "user-update-failed",
                Message = "User not found.",
            });
        }

        var result = await this.userService.UpdateUserAsync(user.UserName, model);

        if (!result)
        {
            this.logger.LogWarning("Updating user with id {UserId} failed.", id);

            return this.BadRequest(new ResponseModel
            {
                Status = "update-failed",
            });
        }

        this.logger.LogInformation("Updating user with id {UserId} succeeded.", id);

        return this.Ok(new ResponseModel
        {
            Status = "update-success",
            Message = "User updated successfully.",
        });
    } 
    
    /// <summary>
    /// Gets all admins.
    /// </summary>
    /// <returns>Response with the result.</returns>
    [HttpGet("admin")]
    [Authorize(Policy = UserPolicies.AdminPermissions)]
    public async Task<ActionResult<IEnumerable<UserVM>>> GetAdminsAsync()
    {
        var result = await this.userService.GetAllAdminsAsync();

        return this.Ok(result);
    }
    
    /// <summary>
    /// Gets all users.
    /// </summary>
    /// <returns>Response with the result.</returns>
    [HttpGet("user")]
    [Authorize(Policy = UserPolicies.AdminPermissions)]
    public async Task<ActionResult<IEnumerable<UserVM>>> GetUsersAsync()
    {
        var result = await this.userService.GetAllUsersAsync();

        return this.Ok(result);
    }
    
    
    /// <summary>
    /// Deletes an user.
    /// </summary>
    /// <param name="id">Id of the user to be deleted.</param>
    /// <returns>Response</returns>
    [HttpDelete("{id}")]
    [Authorize(Policy = UserPolicies.AdminPermissions)]
    public async Task<ActionResult<ResponseModel>> DeleteUserAsync(string id)
    {
        this.logger.LogInformation("Deleting user with id {UserId}", id);

        var user = await this.userService.GetUserByIdAsync(id);
        
        if (user is null)
        {
            this.logger.LogWarning("Deleting user with id {UserId} failed. User not found.", id);

            return this.NotFound(new ResponseModel
            {
                Status = "user-delete-failed",
                Message = "User not found.",
            });
        }
        
        var userRoles = await this.userService.GetUserRolesByIdAsync(id);

        var result = await this.userService.DeleteUserAsync(id);

        if (!result.Succeeded)
        {
            this.logger.LogWarning("Deleting user with id {UserId} failed.", id);

            return this.BadRequest(new ResponseModel
            {
                Status = "delete-failed",
                Message = result.Errors.FirstOrDefault()?.Description,
            });
        }

        this.logger.LogInformation("Deleting user with id {UserId} succeeded.", id);

        return this.Ok(new ResponseModel
        {
            Status = "delete-success",
            Message = "User deleted successfully.",
        });
    }
}