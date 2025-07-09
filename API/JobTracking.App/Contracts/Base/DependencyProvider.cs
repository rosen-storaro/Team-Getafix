using JobTracking.DataAccess;

namespace JobTracking.App.Contracts.Base;

public class DependencyProvider
{
    public DependencyProvider(ApplicationDbContext dbContext)
    {
        Db = dbContext;
    }

    public ApplicationDbContext Db { get; set; }
}