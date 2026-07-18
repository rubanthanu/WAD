import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { Users, UserCheck, Calendar, Activity, ClipboardList, Settings, Briefcase, ChevronRight, AlertCircle } from 'lucide-react';

export default function AdminDashboard() {
  const { user, apiFetch } = useContext(AuthContext);

  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await apiFetch('/stats.php');
      setStats(data);
    } catch (err) {
      setError('Failed to aggregate system statistics.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Header */}
      <div>
        <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Admin Dashboard</h1>
        <p>Welcome back, <strong>{user?.name}</strong>. Here is the operational health overview.</p>
      </div>

      {/* error alert */}
      {error && (
        <div className="alert alert-error" style={{ maxWidth: '600px', width: '100%' }}>
          <AlertCircle size={18} />
          <span>{error}</span>
        </div>
      )}

      {/* Metrics grid */}
      {loading ? (
        <p>Loading operational metrics...</p>
      ) : (
        <div className="grid-4">
          <div className="glass-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem', padding: '1.25rem' }}>
            <div style={{ background: 'rgba(59, 130, 246, 0.15)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '10px' }}>
              <Users size={22} />
            </div>
            <div>
              <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{stats?.total_patients || 0}</h3>
              <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>Registered Patients</p>
            </div>
          </div>

          <div className="glass-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem', padding: '1.25rem' }}>
            <div style={{ background: 'rgba(16, 185, 129, 0.15)', color: 'var(--secondary)', padding: '0.75rem', borderRadius: '10px' }}>
              <UserCheck size={22} />
            </div>
            <div>
              <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{stats?.total_doctors || 0}</h3>
              <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>Active Doctors</p>
            </div>
          </div>

          <div className="glass-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem', padding: '1.25rem' }}>
            <div style={{ background: 'rgba(6, 182, 212, 0.15)', color: 'var(--info)', padding: '0.75rem', borderRadius: '10px' }}>
              <Briefcase size={22} />
            </div>
            <div>
              <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{stats?.total_departments || 0}</h3>
              <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>Departments</p>
            </div>
          </div>

          <div className="glass-card" style={{ display: 'flex', alignItems: 'center', gap: '1.25rem', padding: '1.25rem' }}>
            <div style={{ background: 'rgba(245, 158, 11, 0.15)', color: 'var(--warning)', padding: '0.75rem', borderRadius: '10px' }}>
              <Calendar size={22} />
            </div>
            <div>
              <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{stats?.total_appointments || 0}</h3>
              <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>Total Appointments</p>
            </div>
          </div>
        </div>
      )}

      {/* Operations Panel Links */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
        <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Management Operations</h2>
        
        <div className="grid-3">
          
          {/* Manage Appointments Card */}
          <Link to="/admin/appointments" className="glass-card" style={{
            display: 'flex',
            flexDirection: 'column',
            gap: '1rem',
            textDecoration: 'none',
            transition: 'var(--transition-fast)'
          }}>
            <div style={{ background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', width: '40px', height: '40px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifycontent: 'center', justifyContent: 'center' }}>
              <ClipboardList size={20} />
            </div>
            <div>
              <h3 style={{ fontSize: '1.2rem', margin: 0, display: 'flex', alignItems: 'center', gap: '0.25rem', color: 'var(--text-main)' }}>
                All Appointments <ChevronRight size={16} />
              </h3>
              <p style={{ fontSize: '0.85rem', marginTop: '0.25rem' }}>Review all scheduled appointments, edit statuses, or delete records.</p>
            </div>
          </Link>

          {/* Manage Doctors Card */}
          <Link to="/admin/doctors" className="glass-card" style={{
            display: 'flex',
            flexDirection: 'column',
            gap: '1rem',
            textDecoration: 'none',
            transition: 'var(--transition-fast)'
          }}>
            <div style={{ background: 'rgba(16, 185, 129, 0.1)', color: 'var(--secondary)', width: '40px', height: '40px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifycontent: 'center', justifyContent: 'center' }}>
              <Settings size={20} />
            </div>
            <div>
              <h3 style={{ fontSize: '1.2rem', margin: 0, display: 'flex', alignItems: 'center', gap: '0.25rem', color: 'var(--text-main)' }}>
                Manage Doctors <ChevronRight size={16} />
              </h3>
              <p style={{ fontSize: '0.85rem', marginTop: '0.25rem' }}>Add new doctor users, specify specializations, and configure schedule limits.</p>
            </div>
          </Link>

          {/* Manage Departments Card */}
          <Link to="/admin/departments" className="glass-card" style={{
            display: 'flex',
            flexDirection: 'column',
            gap: '1rem',
            textDecoration: 'none',
            transition: 'var(--transition-fast)'
          }}>
            <div style={{ background: 'rgba(6, 182, 212, 0.1)', color: 'var(--info)', width: '40px', height: '40px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifycontent: 'center', justifyContent: 'center' }}>
              <Activity size={20} />
            </div>
            <div>
              <h3 style={{ fontSize: '1.2rem', margin: 0, display: 'flex', alignItems: 'center', gap: '0.25rem', color: 'var(--text-main)' }}>
                Manage Departments <ChevronRight size={16} />
              </h3>
              <p style={{ fontSize: '0.85rem', marginTop: '0.25rem' }}>Create clinical departments, update designations, and overview details.</p>
            </div>
          </Link>

        </div>
      </div>

    </div>
  );
}
