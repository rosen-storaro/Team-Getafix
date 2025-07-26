// Program.cs
// This is the main entry point for the ASP.NET Core Web API.
// It sets up the web server, dependency injection, and middleware.

var builder = WebApplication.CreateBuilder(args);

// --- Service Registration ---

// Add services to the dependency injection container.
// This tells the application how to create instances of our services.
builder.Services.AddControllers();

// Use an in-memory collection as a simple "database" for this example.
// We register it as a singleton so the data persists for the lifetime of the application.
builder.Services.AddSingleton<InMemoryDataStore>();

// Register our custom services for dependency injection.
// Scoped means a new instance is created for each HTTP request.
builder.Services.AddScoped<EquipmentService>();
builder.Services.AddScoped<RequestService>();
builder.Services.AddScoped<AuthService>();

// Add services for API documentation (Swagger/OpenAPI).
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// --- CORS (Cross-Origin Resource Sharing) Configuration ---
// This is crucial for allowing the HTML/JS frontend to communicate with this backend.
builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowAllOrigins",
        builder =>
        {
            builder.AllowAnyOrigin()
                   .AllowAnyMethod()
                   .AllowAnyHeader();
        });
});

var app = builder.Build();

// --- Middleware Pipeline Configuration ---

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment())
{
    // Use Swagger for API documentation and testing in development.
    app.UseSwagger();
    app.UseSwaggerUI();
}

// Enable the CORS policy we defined above.
app.UseCors("AllowAllOrigins");

app.UseHttpsRedirection();

// This middleware enables routing for controllers.
app.MapControllers();

app.Run();
