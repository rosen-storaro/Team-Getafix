using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using JobTracking.Domain.Enums;
namespace JobTracking.DataAccess.Data.Base
{
    public interface IUser
    {
        int Id { get; set; }

        string FirstName { get; set; }
        string MiddleName { get; set; }
        string LastName { get; set; }
        string Username { get; set; }
        string PasswordHash { get; set; }

        UserRole Role { get; set; }
    }
}
