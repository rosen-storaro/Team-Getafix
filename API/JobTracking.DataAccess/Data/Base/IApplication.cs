using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JobTracking.DataAccess.Data.Base
{
    public enum ApplicationStatus
    {
        Submitted,
        Interview,
        Rejected,
        Accepted
    }

    public interface IApplication
    {
        int Id { get; set; }

        int UserId { get; set; }

        ApplicationStatus Status { get; set; }
    }
}
