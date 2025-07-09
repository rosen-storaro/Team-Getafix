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
    /// Represents a policy for users with accountant permissions. This policy grants access to features that
    /// are specific to accountants, such as viewing analytics and sales of products.
    /// </summary>
    public const string AccountantPermissions = "AccountantPermissions";
    
    /// <summary>
    /// Represents a policy for general users of the application. This policy grants access to general features
    /// that do not require accountant or administrative privileges.
    /// </summary>
    public const string UserPermissions = "UserPermissions";
    
    /// <summary>
    /// Represents a policy for users with elevated permissions. This policy grants access to features that
    /// are both for accountants and general users, but do not require administrative privileges.
    /// </summary>
    public const string NormalPermissions = "NormalPermissions";
    
    /// <summary>
    /// Represents a policy for users with elevated permissions. This policy grants access to features that
    /// are both for accountants and administrators, but do not require general user privileges.
    /// </summary>
    public const string ElevatedPermissions = "ElevatedPermissions";
    
}