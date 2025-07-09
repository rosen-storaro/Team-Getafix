var builder = WebApplication.CreateBuilder(args);

builder.AddServiceDefaults();

builder.Services.AddReverseProxy()
    .LoadFromConfig(builder.Configuration.GetSection("ReverseProxy"))
    .AddServiceDiscoveryDestinationResolver();

var app = builder.Build();

app.UseForwardedHeaders();

app.MapDefaultEndpoints();
app.MapReverseProxy();

app.MapGet("/", () => "Hello World!");

await app.RunAsync();