using Getafix.Api.Services.Users.Data.Data;
using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Shared.Models.User;
using Getafix.Api.Services.Shared.Data.Models.Identity;
using AutoMapper;
using Getafix.Api.Services.Users.Data.Models;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;

namespace Getafix.Api.Services.Users.Services.Implementations;

internal class UserService : IUserService
{
    private readonly UserManager<User> userManager;
    private readonly IMapper mapper;
    private readonly ApplicationDbContext context;

    /// <summary>
    /// Initializes a new instance of the <see cref="UserService"/> class.
    /// </summary>
    /// <param name="userManager">User Manager.</param>
    /// <param name="mapper">AutoMapper.</param>
    /// <param name="context">Db Context.</param>
    public UserService(
        UserManager<User> userManager,
        IMapper mapper,
        ApplicationDbContext context)
    {
        this.userManager = userManager;
        this.mapper = mapper;
        this.context = context;
    }
    
    /// <inheritdoc/>
    public async Task<UserVM?> GetUserByIdAsync(string id)
    {
        var admin = await this.userManager.FindByIdAsync(id);

        return admin is null ? null : this.mapper.Map<UserVM>(admin);
    }

    /// <inheritdoc/>
    public async Task<string> GetUserUsernameByIdAsync(string id)
    {
        var user = await this.userManager.FindByIdAsync(id);

        return await this.userManager.GetUserNameAsync(user);
    }

    /// <inheritdoc/>
    public async Task<bool> ValidateOneTimeTokenForUserAsync(string userId, string token, string type, string purpose)
    {
        var user = await this.userManager.FindByIdAsync(userId);
        
        return await this.userManager.VerifyUserTokenAsync(user, type, purpose, token);
    }

    /// <inheritdoc/>
    public async Task<IdentityResult> ChangePasswordAsync(string userId, string newPassword)
    {
        var user = await this.userManager.FindByIdAsync(userId);

        var token = await this.userManager.GeneratePasswordResetTokenAsync(user);

        return await this.userManager.ResetPasswordAsync(user, token, newPassword);
    }

    /// <inheritdoc/>
    public async Task<bool> UpdateUserAsync(string username, UserUM newUserInfo)
    {
        var user = await this.userManager.FindByNameAsync(username);

        if (user is null)
        {
            return false;
        }
        
        user.UserName = newUserInfo.UserName;

        if (!string.IsNullOrEmpty(newUserInfo.Password))
        {
            await this.ChangePasswordAsync(user.Id, newUserInfo.Password);
        }

        await this.userManager.UpdateAsync(user);
        return true;
    }

    /// <inheritdoc/>
    public async Task<string> GenerateOneTimeTokenForUserAsync(string username, string type, string purpose)
    {
        var user = await this.userManager.FindByNameAsync(username);

        return await this.userManager.GenerateUserTokenAsync(user, type, purpose);
    }

    /// <inheritdoc/>
    public async Task<UserVM> GetUserByUsernameAsync(string username)
    {
        return this.mapper.Map<UserVM>(await this.userManager.FindByNameAsync(username));
    }

    /// <inheritdoc/>
    public async Task<List<UserVM>> GetAllAdminsAsync()
    {
        var admins = await this.userManager.GetUsersInRoleAsync(UserRoles.Admin);
        
        return admins.Select(user => this.mapper.Map<UserVM>(user)).ToList();
    }
    
    /// <inheritdoc/>
    public async Task<List<UserVM>> GetAllUsersAsync()
    {
        var users = await this.userManager.GetUsersInRoleAsync(UserRoles.User);

        return users.Select(user => this.mapper.Map<UserVM>(user)).ToList();
    }

    /// <inheritdoc/>
    public async Task<IdentityResult> DeleteUserAsync(string id)
    {
        var rts = await this.context.RefreshTokens.Where(rt => rt.UserId == id).ToListAsync();

        context.RemoveRange(rts);

        await context.SaveChangesAsync();
        
        var user = await this.userManager.FindByIdAsync(id);
        
        return await this.userManager.DeleteAsync(user!);
    }

    /// <inheritdoc/>
    public async Task<List<string>> GetUserRolesByIdAsync(string id)
    {
        var admin = await this.userManager.FindByIdAsync(id);
        
        if (admin is null)
        {
            return [];
        }

        return (await this.userManager.GetRolesAsync(admin)).ToList();
    }
}