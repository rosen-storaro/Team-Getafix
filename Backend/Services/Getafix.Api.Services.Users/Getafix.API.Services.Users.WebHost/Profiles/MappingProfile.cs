using Getafix.Api.Services.Users.Data.Models.Identity;
using Getafix.Api.Services.Users.Shared.Models.User;
using AutoMapper;
using Getafix.Api.Services.Users.Data.Models;

namespace Getafix.Api.Services.Users.WebHost.Profiles;

/// <summary>
/// Mapping profile.
/// </summary>
public class MappingProfile : Profile
{
    /// <summary>
    /// Initializes a new instance of the <see cref="MappingProfile"/> class.
    /// </summary>
    public MappingProfile()
    {
        this.CreateMap<User, UserVM>();
        this.CreateMap<UserUM, UserVM>();
    }
}
