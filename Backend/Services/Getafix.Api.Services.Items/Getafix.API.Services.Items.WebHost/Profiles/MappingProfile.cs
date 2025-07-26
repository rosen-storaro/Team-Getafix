using AutoMapper;
using Getafix.Api.Services.Items.Data.Models;
using Getafix.Api.Services.Items.Shared.DTOs;

namespace Getafix.Api.Services.Items.WebHost.Profiles;

/// <summary>
/// AutoMapper mapping profile for Items service.
/// </summary>
public class MappingProfile : Profile
{
    /// <summary>
    /// Initializes a new instance of the <see cref="MappingProfile"/> class.
    /// </summary>
    public MappingProfile()
    {
        CreateMap<Item, ItemDto>();
        CreateMap<CreateItemDto, Item>();
        CreateMap<UpdateItemDto, Item>();
    }
}
