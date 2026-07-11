import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { Calendar, Plus, Clock, AlertCircle, XCircle } from 'lucide-react';

export default function PatientDashboard() {
  const { user, apiFetch } = useContext(AuthContext);
  const [appointments, setAppointments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

  useEffect(() => {
    fetchAppointments();
  }, []);

  const fetchAppointments = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await apiFetch('/appointments.php');
      setAppointments(data || []);
    } catch (err) {
      setError('Failed to fetch your appointments.');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async (apptId) => {
    if (!window.confirm('Are you sure you want to cancel this appointment?')) return;

    setActionLoading(true);
    setError('');
    try {
      await apiFetch('/appointments.php', {
        method: 'PUT',
        body: JSON.stringify({
          appointment_id: apptId,
          status: 'cancelled'
        })
      });
      // Refresh list
      await fetchAppointments();
    } catch (err) {
      setError(err.message || 'Failed to cancel appointment.');
    } finally {
      setActionLoading(false);
    }
  };

  // Compute counters
  const total = appointments.length;
  const pending = appointments.filter(a => a.status === 'pending').length;
  const confirmed = appointments.filter(a => a.status === 'confirmed').length;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Header Panel */}
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        flexWrap: 'wrap',
        gap: '1rem'
      }}>
        <div>
          <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Patient Dashboard</h1>
          <p>Welcome back, <strong>{user?.name}</strong>. Here is your medical schedule.</p>
        </div>
        <Link to="/patient/book" className="btn btn-primary">
          Book Appointment <Plus size={18} />
        </Link>
      </div>

      {/* Mini stats cards */}
      <div className="grid-3">
        <div className="glass-card" style={{ padding: '1.25rem 1.5rem', display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <div style={{ background: 'rgba(59, 130, 246, 0.15)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '10px' }}>
            <Calendar size={22} />
          </div>
          <div>
            <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{total}</h3>
            <p style={{ margin: 0, fontSize: '0.85rem' }}>Total Bookings</p>
          </div>
        </div>

        <div className="glass-card" style={{ padding: '1.25rem 1.5rem', display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <div style={{ background: 'rgba(245, 158, 11, 0.15)', color: 'var(--warning)', padding: '0.75rem', borderRadius: '10px' }}>
            <Clock size={22} />
          </div>
          <div>
            <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{pending}</h3>
            <p style={{ margin: 0, fontSize: '0.85rem' }}>Pending Confirmation</p>
          </div>
        </div>

        <div className="glass-card" style={{ padding: '1.25rem 1.5rem', display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <div style={{ background: 'rgba(16, 185, 129, 0.15)', color: 'var(--secondary)', padding: '0.75rem', borderRadius: '10px' }}>
            <Clock size={22} />
          </div>
          <div>
            <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{confirmed}</h3>
            <p style={{ margin: 0, fontSize: '0.85rem' }}>Confirmed Schedules</p>
          </div>
        </div>
      </div>

      {/* error alert */}
      {error && (
        <div className="alert alert-error">
          <AlertCircle size={18} />
          <span>{error}</span>
        </div>
      )}

      {/* Appointments schedule table */}
      <div className="glass-card">
        <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>Your Booked Appointments</h2>

        {loading ? (
          <p>Fetching scheduled appointments...</p>
        ) : appointments.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '2rem' }}>
            <p style={{ marginBottom: '1rem' }}>You don't have any booked appointments yet.</p>
            <Link to="/patient/book" className="btn btn-outline" style={{ fontSize: '0.85rem' }}>
              Book Your First Appointment
            </Link>
          </div>
        ) : (
          <div className="table-container">
            <table className="custom-table">
              <thead>
                <tr>
                  <th>Doctor</th>
                  <th>Department / Specialization</th>
                  <th>Date & Time</th>
                  <th>Notes</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {appointments.map((appt) => (
                  <tr key={appt.id}>
                    <td style={{ fontWeight: 600, color: 'var(--text-main)' }}>{appt.doctor_name}</td>
                    <td>
                      <div>{appt.department_name}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.specialization}</div>
                    </td>
                    <td>
                      <div>{appt.appointment_date}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.appointment_time}</div>
                    </td>
                    <td style={{ maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      {appt.notes || <span style={{ fontStyle: 'italic' }}>None</span>}
                    </td>
                    <td>
                      <span className={`badge badge-${appt.status}`}>{appt.status}</span>
                    </td>
                    <td>
                      {(appt.status === 'pending' || appt.status === 'confirmed') ? (
                        <button
                          className="btn btn-danger btn-outline"
                          style={{
                            padding: '0.35rem 0.75rem',
                            fontSize: '0.75rem',
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: '0.25rem',
                            boxShadow: 'none'
                          }}
                          onClick={() => handleCancel(appt.id)}
                          disabled={actionLoading}
                        >
                          <XCircle size={14} /> Cancel
                        </button>
                      ) : (
                        <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontStyle: 'italic' }}>Closed</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </div>
  );
}
