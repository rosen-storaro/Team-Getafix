using Microsoft.Extensions.Configuration;

namespace Getafix.Api.Aspire.AppHost.Extension;

public static class ResourceBuilderExtensions
{
    public static IResourceBuilder<T> WithEnvironment<T>(
        this IResourceBuilder<T> builder, 
        string rootKey) where T : IResourceWithEnvironment
    {
        var targetedConfiguration = builder.ApplicationBuilder.Configuration.GetSection(rootKey);
        
        var configValues = new Dictionary<string, string>();
        LoadConfigurationSectionsAsKeyValuePairs(targetedConfiguration.GetChildren(), configValues, rootKey);

        // For each configuration setting, call WithEnvironment to set it
        foreach (var configValue in configValues)
        {
            builder = builder.WithEnvironment(context =>
            {
                context.EnvironmentVariables[configValue.Key] = configValue.Value;
            });
        }

        return builder;
    }

    private static void LoadConfigurationSectionsAsKeyValuePairs(IEnumerable<IConfigurationSection> sections, IDictionary<string, string> keyValuePairs, string prefix)
    {
        foreach (var section in sections)
        {
            var key = string.IsNullOrEmpty(prefix) ? section.Key : $"{prefix}__{section.Key}";
            
            if (section.Value != null)
                keyValuePairs[key] = section.Value;
            else
                LoadConfigurationSectionsAsKeyValuePairs(section.GetChildren(), keyValuePairs, key);
        }
    }
}