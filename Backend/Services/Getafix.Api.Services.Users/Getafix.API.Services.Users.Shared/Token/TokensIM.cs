namespace Getafix.Api.Services.Users.Shared.Models.Token;

/// <summary>
/// Represents the input model for a Tokens.
/// </summary>
public class TokensIM
{
    /// <summary>
    /// Gets or sets access token.
    /// </summary>
    public string AccessToken { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets refresh token.
    /// </summary>
    public string RefreshToken { get; set; } = string.Empty;
}