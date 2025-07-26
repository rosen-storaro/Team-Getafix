using Getafix.Api.Services.Items.Data.Data;
using Getafix.Api.Services.Items.Data.Models;
using Getafix.Api.Services.Items.Services.Contracts;
using Microsoft.EntityFrameworkCore;

namespace Getafix.Api.Services.Items.Services.Implementations;

/// <summary>
/// Implementation of the item service.
/// </summary>
public class ItemService : IItemService
{
    private readonly ApplicationDbContext _context;

    /// <summary>
    /// Initializes a new instance of the <see cref="ItemService"/> class.
    /// </summary>
    /// <param name="context">The database context.</param>
    public ItemService(ApplicationDbContext context)
    {
        _context = context;
    }

    /// <inheritdoc />
    public async Task<IEnumerable<Item>> GetAllItemsAsync()
    {
        return await _context.Items.ToListAsync();
    }

    /// <inheritdoc />
    public async Task<Item?> GetItemByIdAsync(int id)
    {
        return await _context.Items.FindAsync(id);
    }

    /// <inheritdoc />
    public async Task<Item> CreateItemAsync(Item item)
    {
        item.CreatedAt = DateTime.UtcNow;
        item.UpdatedAt = DateTime.UtcNow;
        
        _context.Items.Add(item);
        await _context.SaveChangesAsync();
        return item;
    }

    /// <inheritdoc />
    public async Task<Item> UpdateItemAsync(Item item)
    {
        item.UpdatedAt = DateTime.UtcNow;
        
        _context.Items.Update(item);
        await _context.SaveChangesAsync();
        return item;
    }

    /// <inheritdoc />
    public async Task<bool> DeleteItemAsync(int id)
    {
        var item = await _context.Items.FindAsync(id);
        if (item == null)
        {
            return false;
        }

        item.IsDeleted = true;
        item.UpdatedAt = DateTime.UtcNow;
        
        _context.Items.Update(item);
        await _context.SaveChangesAsync();
        return true;
    }

    /// <inheritdoc />
    public async Task<IEnumerable<Item>> GetItemsByCategoryAsync(string category)
    {
        return await _context.Items
            .Where(i => i.Category == category)
            .ToListAsync();
    }
}
