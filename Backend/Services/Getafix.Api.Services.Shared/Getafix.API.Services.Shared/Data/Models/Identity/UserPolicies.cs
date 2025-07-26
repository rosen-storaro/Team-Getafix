namespace Getafix.Api.Services.Shared.Data.Models.Identity;

/// <summary>
/// Defines constants for user policies within the application. These policies are used to control access
/// to various parts of the application based on the roles and permissions assigned to a user.
/// </summary>
public static class UserPolicies
{
    /// <summary>
    /// Represents a policy specifically for administrators with elevated permissions. This policy grants
    /// the highest level of access, intended for users who manage and configure the application.
    /// </summary>
    public const string AdminPermissions = "AdminPermissions";
    
    /// <summary>
    /// Represents a policy for general users of the application. This policy grants access to general features
    /// that do not require accountant or administrative privileges.
    /// </summary>
    public const string UserPermissions = "UserPermissions";
    
    public const string ManagerPermissions = "ManagerPermissions";
}