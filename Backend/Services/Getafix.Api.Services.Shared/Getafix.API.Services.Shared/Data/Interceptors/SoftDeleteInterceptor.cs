using Getafix.Api.Services.Shared.Data.Interfaces;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Diagnostics;

namespace Getafix.Api.Services.Shared.Data.Interceptors;

/// <summary>
/// A custom EF Core SaveChanges interceptor for implementing soft delete functionality.
/// This interceptor modifies the entity state to 'Modified' and sets the 'IsDeleted' flag to true
/// for entities that implement the ISoftDelete interface, instead of actually deleting them from the database.
/// </summary>
public class SoftDeleteInterceptor : SaveChangesInterceptor
{
    /// <summary>
    /// Overrides the SavingChanges method to implement soft delete logic.
    /// </summary>
    /// <param name="eventData">Provides data for the saving changes event.</param>
    /// <param name="result">The original interception result.</param>
    /// <returns>The possibly modified interception result.</returns>
    public override InterceptionResult<int> SavingChanges(
        DbContextEventData eventData, 
        InterceptionResult<int> result)
    {
        if (eventData.Context is null) return result;
        
        foreach (var entry in eventData.Context.ChangeTracker.Entries())
        {
            if (entry is not { State: EntityState.Deleted, Entity: ISoftDelete delete }) continue;
            entry.State = EntityState.Modified;
            delete.IsDeleted = true;
        }
        return result;
    }

    public override ValueTask<InterceptionResult<int>> SavingChangesAsync(
        DbContextEventData eventData,
        InterceptionResult<int> result,
        CancellationToken cancellationToken = default)
    {
        if (eventData.Context is null) return ValueTask.FromResult(result);
        
        foreach (var entry in eventData.Context.ChangeTracker.Entries())
        {
            if (entry is not { State: EntityState.Deleted, Entity: ISoftDelete delete }) continue;
            entry.State = EntityState.Modified;
            delete.IsDeleted = true;
        }
        return ValueTask.FromResult(result);
    }
}