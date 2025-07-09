using System.Diagnostics;
using Getafix.Api.Services.Users.Data.Data;
using Getafix.Api.Services.Users.Services.Contracts;
using Getafix.Api.Services.Users.Shared.Models.User;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Storage;
using OpenTelemetry.Trace;

namespace Getafix.Api.Services.Users.MigrationService;

public class Worker(
    IServiceProvider serviceProvider,
    IHostApplicationLifetime hostApplicationLifetime)
    : BackgroundService
{
    internal const string ActivitySourceName = "Migrations";
    private static readonly ActivitySource s_activitySource = new(ActivitySourceName);

    protected override async Task ExecuteAsync(CancellationToken cancellationToken)
    {
        using var activity = s_activitySource.StartActivity("Migrating database",
            ActivityKind.Client);
        try
        {
            using var scope = serviceProvider.CreateScope();
            var dbContext = scope.ServiceProvider.GetRequiredService<ApplicationDbContext>();
            var authService = scope.ServiceProvider.GetRequiredService<IAuthService>();
            var userService = scope.ServiceProvider.GetRequiredService<IUserService>();
            var configuration = scope.ServiceProvider.GetRequiredService<IConfiguration>();
            await EnsureDatabaseAsync(dbContext,
                cancellationToken);
            await RunMigrationAsync(dbContext,
                cancellationToken);
            await SeedDataAsync(dbContext,
                authService,
                userService,
                configuration,
                cancellationToken);
        }
        catch (Exception ex)
        {
            activity?.RecordException(ex);
            throw;
        }

        hostApplicationLifetime.StopApplication();
    }

    private static async Task EnsureDatabaseAsync(ApplicationDbContext dbContext,
        CancellationToken cancellationToken)
    {
        var dbCreator = dbContext.GetService<IRelationalDatabaseCreator>();
    
        if (!await dbCreator.ExistsAsync(cancellationToken))
        {
            await dbCreator.CreateAsync(cancellationToken);
        }
    }

    private static async Task RunMigrationAsync(ApplicationDbContext dbContext,
        CancellationToken cancellationToken)
    {
        await dbContext.Database.MigrateAsync(cancellationToken);
    }

    private static async Task SeedDataAsync(ApplicationDbContext dbContext,
        IAuthService authService,
        IUserService userService,
        IConfiguration configuration,
        CancellationToken cancellationToken)
    {
        var admins = await userService.GetAllAdminsAsync();
        if (admins.Count == 0)
        {
            UserIM admin = new()
            {
                UserName = configuration.GetValue<string>("SuperAdmin:Username"),
                Password = configuration.GetValue<string>("SuperAdmin:Password"),
            };
            await authService.CreateAdminAsync(admin);
        }
        /*
       var strategy = dbContext.Database.CreateExecutionStrategy();
       await strategy.ExecuteAsync(async () =>
       {
           // Seed the database
           await using var transaction = await dbContext.Database.BeginTransactionAsync(cancellationToken);
           await dbContext.Tickets.AddAsync(firstTicket, cancellationToken);
           await dbContext.SaveChangesAsync(cancellationToken);
           await transaction.CommitAsync(cancellationToken);
       });*/
    }
}