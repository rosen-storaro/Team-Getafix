using Getafix.Api.Services.Items.Data.Data;
using Getafix.Api.Services.Items.Services;
using Getafix.Api.Services.Items.WebHost.Profiles;
using Getafix.Api.Services.Shared.Data.Interceptors;

var builder = WebApplication.CreateBuilder(args);
var configuration = builder.Configuration;

builder.AddServiceDefaults();
builder.AddNpgsqlDbContext<ApplicationDbContext>("items-db", default, options =>
{
    options.AddInterceptors(new SoftDeleteInterceptor());
});

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();
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

app.UseAuthorization();

app.MapControllers();

app.Logger.LogInformation("Starting the Items service.");

app.Run();
