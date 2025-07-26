using System.Text;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Getafix.Api.EventBus.Abstraction.Extensions;
// using Getafix.Api.EventBus.RabbitMQ;
using Getafix.Api.Services.Users.Data.Data;
using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.Services;
// using Getafix.Api.Services.Users.WebHost.Handlers;
using Getafix.Api.Services.Users.WebHost.Profiles;
using Getafix.Api.Services.Users.WebHost.SwaggerConfiguration;
using Getafix.Api.Services.Shared.Data.Interceptors;
using Getafix.Api.Services.Shared.Data.Models.Identity;
using Getafix.Api.Services.Users.Data.Models;
// using Getafix.Api.Services.Shared.IntegrationEvent;
// using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.AspNetCore.Identity;
using Microsoft.IdentityModel.Tokens;

var builder = WebApplication.CreateBuilder(args);
var configuration = builder.Configuration;

builder.AddServiceDefaults();
builder.AddNpgsqlDbContext<ApplicationDbContext>("identity-db", default, options =>
{
    options.AddInterceptors(new SoftDeleteInterceptor());
});

builder.Services
    .AddIdentity<User, IdentityRole>(options =>
    {
        options.User.RequireUniqueEmail = false;
    })
    .AddEntityFrameworkStores<ApplicationDbContext>()
    .AddDefaultTokenProviders();
    
    
builder.Services
    .AddAuthentication(options =>
    {
        options.DefaultAuthenticateScheme = JwtBearerDefaults.AuthenticationScheme;
        options.DefaultChallengeScheme = JwtBearerDefaults.AuthenticationScheme;
        options.DefaultScheme = JwtBearerDefaults.AuthenticationScheme;
    })
    .AddJwtBearer(options =>
    {
        options.SaveToken = true;
        options.RequireHttpsMetadata = false;
        options.TokenValidationParameters = new TokenValidationParameters()
        {
            ValidateIssuer = false,
            ValidateAudience = false,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,
            ClockSkew = TimeSpan.Zero,
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(configuration["JWT:AccessTokenSecret"]!)),
        };
    });

builder.Services.AddAuthorization(options =>
{
    options.AddPolicy(UserPolicies.UserPermissions, policy =>
        policy.RequireRole(UserRoles.User)); 
    options.AddPolicy(UserPolicies.AdminPermissions, policy =>
        policy.RequireRole(UserRoles.Admin));
});

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwagger();
builder.Services.AddServices();
builder.Services.AddHttpContextAccessor();

builder.Services.AddAutoMapper(typeof(MappingProfile));

var app = builder.Build();


app.MapDefaultEndpoints();

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}
else
{
    app.UseHttpsRedirection();
}


app.UseAuthentication();
app.UseAuthorization();

app.MapControllers();

app.Logger.LogInformation("Starting the app.");

app.Run();