using Getafix.Api.Aspire.AppHost.Extension;

var builder = DistributedApplication.CreateBuilder(args);

var rabbitMqUsername = builder.AddParameter("database-username");
var rabbitMqPassword = builder.AddParameter("database-password", secret: true);

var identityDb = builder.AddPostgres("identity-db-server", password: rabbitMqPassword)
    .WithDataVolume()
    .AddDatabase("identity-db");

var itemsDb = builder.AddPostgres("items-db-server", password: rabbitMqPassword)
    .WithDataVolume()
    .AddDatabase("items-db");

var rabbitMq = builder.AddRabbitMQ(
        "Getafix-eventbus",
        rabbitMqUsername,
        rabbitMqPassword,
        5849)
    .WithManagementPlugin();

builder.AddProject<Projects.Getafix_API_Services_Users_MigrationServices>("identity-migration-service")
    .WithReference(identityDb)
    .WithEnvironment("SuperAdmin");


var identityService = builder
    .AddProject<Projects.Getafix_API_Services_Users_WebHost>("identity-service")
    .WithReference(identityDb)
    .WithReference(rabbitMq)
    .WithEnvironment("JWT");

builder
    .AddProject<Projects.Getafix_API_Gateway_WebHost>("api-gateway")
    .WithReference(identityService);

builder.Build().Run();