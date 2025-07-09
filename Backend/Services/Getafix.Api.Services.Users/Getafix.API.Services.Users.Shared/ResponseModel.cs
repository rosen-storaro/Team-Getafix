namespace Getafix.Api.Services.Users.Shared.Models;

/// <summary>
/// An API Response.
/// </summary>
public class ResponseModel
{
    /// <summary>
    /// Gets or sets the status of the response.
    /// </summary>
    public string Status { get; set; } = string.Empty;

    /// <summary>
    /// Gets or sets the message of the response.
    /// </summary>
    public string Message { get; set; } = string.Empty;
}