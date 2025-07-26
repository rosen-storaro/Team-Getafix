using Getafix.Api.Services.Items.Data.Data;
using Getafix.Api.Services.Items.MigrationServices;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;

Thread.Sleep(10000);

var builder = Host.CreateApplicationBuilder(args);

builder.AddServiceDefaults();
builder.Services.AddHostedService<Worker>();

builder.Services.AddOpenTelemetry()
    .WithTracing(tracing => tracing.AddSource(Worker.ActivitySourceName));

builder.AddNpgsqlDbContext<ApplicationDbContext>("");

var host = builder.Build();
host.Run();