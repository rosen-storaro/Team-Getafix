namespace Getafix.Api.Services.Shared.Data.Interfaces;

/// <summary>
/// Defines the interface for entities that support soft deletion. Soft deletion is a way to mark entities as deleted
/// without actually removing them from the database. This allows for data recovery and audit trails.
/// </summary>
public interface ISoftDelete
{
    /// <summary>
    /// Gets or sets a value indicating whether the entity is marked as deleted.
    /// </summary>
    /// <value><c>true</c> if the entity is marked as deleted; otherwise, <c>false</c>.</value>
    public bool IsDeleted { get; set; }

    /// <summary>
    /// Restores a soft-deleted entity to its undeleted state. This method sets the <see cref="IsDeleted"/>
    /// property to <c>false</c>, effectively "undoing" the deletion.
    /// </summary>
    public void Undo()
    {
        IsDeleted = false;
    }
}