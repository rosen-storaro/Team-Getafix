using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Getafix.Api.Services.Users.Data.Data;
using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Shared.Models.Token;
using Getafix.Api.Services.Shared.Data.Models.Identity;
using Getafix.Api.Services.Users.Data.Models;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.IdentityModel.Tokens;

namespace Getafix.Api.Services.Users.Services.Implementations;


/// <summary>
/// Class that implements <see cref="ITokenService"/>.
/// </summary>
internal class TokenService : ITokenService
{
    private readonly IConfiguration configuration;
    private readonly UserManager<User> userManager;
    private readonly ApplicationDbContext context;
    
    /// <summary>
    /// Initializes a new instance of the <see cref="TokenService"/> class.
    /// </summary>
    /// <param name="configuration">Configuration.</param>
    /// <param name="userManager">User manager.</param>
    /// <param name="context">DB Context.</param>
    public TokenService(
        IConfiguration configuration,
        UserManager<User> userManager,
        ApplicationDbContext context)
    {
        this.configuration = configuration;
        this.userManager = userManager;
        this.context = context;
    }
    
    /// <inheritdoc/>
    public async Task<Tokens> CreateTokensForUserAsync(string username)
    {
        var user = await this.userManager.FindByNameAsync(username);

        if (user is null)
        {
            throw new ArgumentException("User not found.");
        }

        var userRoles = await this.userManager.GetRolesAsync(user);

        var authClaims = new List<Claim>
        {
            new Claim(ClaimTypes.NameIdentifier, user.Id),
        };

        foreach (var userRole in userRoles)
        {
            authClaims.Add(new Claim(ClaimTypes.Role, userRole));
        }

        var accessToken = this.CreateToken(authClaims, TokenTypes.AccessToken);
        var refreshToken = this.CreateToken(authClaims, TokenTypes.RefreshToken);

        await this.SaveRefreshTokenAsync(new RefreshToken
        {
            Token = new JwtSecurityTokenHandler().WriteToken(refreshToken),
            UserId = user.Id,
        });

        return new ()
        {
            AccessToken = accessToken,
            RefreshToken = refreshToken,
        };
    }
    
    /// <inheritdoc/>
    public async Task<Tokens> CreateNewTokensAsync(TokensIM tokens)
    {
        var principal = this.GetPrincipalFromExpiredToken(tokens.AccessToken);

        if (principal is null)
        {
            return new ()
            {
                AccessToken = null,
                RefreshToken = null,
            };
        }

        var user = await this.userManager.FindByIdAsync(principal.FindFirst(ClaimTypes.NameIdentifier) !.Value);
        var refreshToken = await this.GetRefreshTokenAsync(tokens.RefreshToken);

        if (user is null || refreshToken is null || refreshToken.UserId != user.Id || !this.ValidateRefreshToken(tokens.RefreshToken))
        {
            return new ()
            {
                AccessToken = null,
                RefreshToken = null,
            };
        }

        var userRoles = await this.userManager.GetRolesAsync(user);

        var authClaims = new List<Claim>
        {
            new Claim(ClaimTypes.NameIdentifier, user.Id),
        };

        foreach (var userRole in userRoles)
        {
            authClaims.Add(new Claim(ClaimTypes.Role, userRole));
        }

        await this.DeleteRefreshTokenAsync(refreshToken);

        var newRefreshToken = this.CreateToken(authClaims, TokenTypes.RefreshToken);

        await this.SaveRefreshTokenAsync(new RefreshToken
        {
            Token = new JwtSecurityTokenHandler().WriteToken(newRefreshToken),
            UserId = user.Id,
        });

        return new ()
        {
            AccessToken = this.CreateToken(authClaims, TokenTypes.AccessToken),
            RefreshToken = newRefreshToken,
        };
    }

    /// <inheritdoc/>
    public async Task SaveRefreshTokenAsync(RefreshToken refreshToken)
    {
        this.context.Add(refreshToken);

        await this.context.SaveChangesAsync();
    }

    /// <inheritdoc/>
    public async Task<RefreshToken?> GetRefreshTokenAsync(string? token)
    {
        return this.context.RefreshTokens?.FirstOrDefault(rt => rt.Token == token);
    }

    /// <inheritdoc/>
    public async Task DeleteRefreshTokenAsync(RefreshToken refreshToken)
    {
        try
        {
            this.context.RefreshTokens?.Remove(refreshToken);

            await this.context.SaveChangesAsync();
        }
        catch (Exception e)
        {
            // ignored
        }
    }
    
    /// <summary>
    /// Validates a refresh token by checking if the token is a JWT token and can be validated against the RefreshTokenSecret key.
    /// </summary>
    /// <param name="token">The refresh token to validate.</param>
    /// <returns>Returns true if refresh token is valid else false.</returns>
    private bool ValidateRefreshToken(string token)
    {
        var tokenValidationParameter = new TokenValidationParameters
        {
            ValidateAudience = false,
            ValidateIssuer = false,
            ValidateIssuerSigningKey = true,
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(this.configuration["JWT:RefreshTokenSecret"] !)),
            ValidateLifetime = false,
        };

        var tokenHandler = new JwtSecurityTokenHandler();
        _ = tokenHandler.ValidateToken(token, tokenValidationParameter, out var securityToken);

        return securityToken is JwtSecurityToken jwtSecurityToken && jwtSecurityToken.Header.Alg.Equals(SecurityAlgorithms.HmacSha256, StringComparison.InvariantCultureIgnoreCase);
    }
    
    /// <summary>
    /// Gets principal from expired token.
    /// </summary>
    /// <param name="token">The token.</param>
    /// <returns>The principles from the token</returns>
    /// <exception cref="SecurityTokenException">Security token exception.</exception>
    private ClaimsPrincipal? GetPrincipalFromExpiredToken(string? token)
    {
        var tokenValidationParameter = new TokenValidationParameters
        {
            ValidateAudience = false,
            ValidateIssuer = false,
            ValidateIssuerSigningKey = true,
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(this.configuration["JWT:AccessTokenSecret"]!)),
            ValidateLifetime = false,
        };

        var tokenHandler = new JwtSecurityTokenHandler();
        var principal = tokenHandler.ValidateToken(token, tokenValidationParameter, out var securityToken);

        if (securityToken is not JwtSecurityToken jwtSecurityToken || !jwtSecurityToken.Header.Alg.Equals(SecurityAlgorithms.HmacSha256, StringComparison.InvariantCultureIgnoreCase))
        {
            throw new SecurityTokenException("Invalid token");
        }

        return principal;
    }
    
    /// <summary>
    /// Create token.
    /// </summary>
    /// <param name="authClaims">Authentication claims.</param>
    /// <param name="tokenType">Token type.</param>
    /// <returns>JWT Security Token.</returns>
    private JwtSecurityToken CreateToken(List<Claim> authClaims, TokenTypes tokenType)
    {
        SymmetricSecurityKey? authSigningKey;

        int tokenValidity = 0;

        if (tokenType == TokenTypes.AccessToken)
        {
            authSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(this.configuration["JWT:AccessTokenSecret"] !));
            _ = int.TryParse(this.configuration["JWT:AccessTokenValidityInMinutes"], out tokenValidity);
        }
        else
        {
            authSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(this.configuration["JWT:RefreshTokenSecret"] !));
            _ = int.TryParse(this.configuration["JWT:RefreshTokenValidityInDays"], out tokenValidity);
        }

        var token = new JwtSecurityToken(
            expires: tokenType == TokenTypes.AccessToken ? DateTime.UtcNow.AddMinutes(tokenValidity) : DateTime.UtcNow.AddDays(tokenValidity),
            claims: authClaims,
            signingCredentials: new SigningCredentials(authSigningKey, SecurityAlgorithms.HmacSha256));

        return token;
    }
}