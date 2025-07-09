using System.Text;
using JobTracking.App.Contracts;
using JobTracking.App.Contracts.Base;
using JobTracking.App.Implementation;
using JobTracking.DataAccess;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;

namespace JobTracking.API
{
    public static class ServiceConfiguratorExtensions
    {
        public static void AddContext(this WebApplicationBuilder builder)
        {
            builder.Services.AddScoped<ApplicationDbContext>();
        }

        public static void AddIdentity(this WebApplicationBuilder builder)
        {
            // Uncommented and configured JWT Authentication
            builder.Services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
                .AddJwtBearer(options =>
                {
                    options.TokenValidationParameters = new TokenValidationParameters
                    {
                        ValidateIssuer = true,
                        ValidateAudience = true,
                        ValidateLifetime = true,
                        ValidateIssuerSigningKey = true,
                        ValidIssuer = builder.Configuration["Jwt:Issuer"],
                        ValidAudience = builder.Configuration["Jwt:Audience"],
                        IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(builder.Configuration["Jwt:Key"]))
                    };
                });

            // Add Authorization policies if needed
            builder.Services.AddAuthorization(options =>
            {
                options.AddPolicy("AdminOnly", policy => policy.RequireRole("Admin"));
                options.AddPolicy("UserOnly", policy => policy.RequireRole("User"));
            });
        }

        public static void AddServices(this WebApplicationBuilder builder)
        {
            builder.Services.AddScoped<DependencyProvider>();
            builder.Services.AddScoped<IUserService, UserService>();
            builder.Services.AddScoped<IJobService, JobService>();
            builder.Services.AddScoped<IAuthService, AuthService>();
            builder.Services.AddScoped<IApplicationService, ApplicationService>();
        }

        public static void AddCors(this WebApplicationBuilder builder)
        {
            builder.Services.AddCors(options =>
            {
                options.AddDefaultPolicy(
                    config =>
                    {
                        config.WithOrigins("http://localhost:4200", "https://localhost:7184")
                            .AllowAnyHeader()
                            .AllowAnyMethod()
                            .AllowCredentials();
                    });
            });
        }
    }
}