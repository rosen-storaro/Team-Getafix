using Getafix.Api.Services.Users.MigrationService;
using Getafix.Api.Services.Users.Services;
using Getafix.Api.Services.Users.Data.Data;
using Getafix.Api.Services.Users.Data.Models;
using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.WebHost.Profiles;
using Microsoft.AspNetCore.Identity;


// Wait for ten seconds to allow the database to start
// TODO: Remove in PROD
Thread.Sleep(10000);

var builder = Host.CreateApplicationBuilder(args);

builder.AddServiceDefaults();
builder.Services.AddHostedService<Worker>();
builder.Services.AddServices();

builder.Services
    .AddIdentity<User, IdentityRole>(options =>
    {
        options.User.RequireUniqueEmail = false;
    })
    .AddEntityFrameworkStores<ApplicationDbContext>()
    .AddDefaultTokenProviders();

builder.Services.AddOpenTelemetry()
    .WithTracing(tracing => tracing.AddSource(Worker.ActivitySourceName));

builder.Services.AddAutoMapper(typeof(MappingProfile));

builder.AddNpgsqlDbContext<ApplicationDbContext>("identity-db");

var host = builder.Build();

host.Run();