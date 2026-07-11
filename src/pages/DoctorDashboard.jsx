import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../App';
import { Calendar, User, Clock, Check, X, RefreshCw, Clipboard, AlertCircle, CheckCircle } from 'lucide-react';

export default function DoctorDashboard() {
  const { user, apiFetch } = useContext(AuthContext);

  const [appointments, setAppointments] = useState([]);
  const [availability, setAvailability] = useState('');
  const [loading, setLoading] = useState(true);
  const [availabilityLoading, setAvailabilityLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    setError('');
    try {
      const [schedule, availDetails] = await Promise.all([
        apiFetch('/appointments.php'),
        apiFetch('/doctors.php?own=true')
      ]);
      setAppointments(schedule || []);
      setAvailability(availDetails?.availability || '');
    } catch (err) {
      setError('Failed to fetch doctor dashboard information.');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateAvailability = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setAvailabilityLoading(true);

    try {
      await apiFetch('/doctors.php', {
        method: 'PUT',
        body: JSON.stringify({
          availability: availability
        })
      });
      setSuccess('Availability updated successfully!');
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Failed to update availability.');
    } finally {
      setAvailabilityLoading(false);
    }
  };

  const handleStatusUpdate = async (apptId, newStatus) => {
    setError('');
    setActionLoading(true);
    try {
      await apiFetch('/appointments.php', {
        method: 'PUT',
        body: JSON.stringify({
          appointment_id: apptId,
          status: newStatus
        })
      });
      // Refresh list
      const schedule = await apiFetch('/appointments.php');
      setAppointments(schedule || []);
    } catch (err) {
      setError(err.message || 'Failed to update appointment status.');
    } finally {
      setActionLoading(false);
    }
  };

  // Stats counters
  const total = appointments.length;
  const pending = appointments.filter(a => a.status === 'pending').length;
  const confirmed = appointments.filter(a => a.status === 'confirmed').length;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Title */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem' }}>
        <div>
          <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Doctor Portal</h1>
          <p>Welcome back, <strong>{user?.name}</strong>. Manage your appointments queue and set availability terms.</p>
        </div>
        <button className="btn btn-outline" onClick={fetchData} disabled={loading} style={{ fontSize: '0.85rem' }}>
          <RefreshCw size={16} /> Refresh Queue
        </button>
      </div>

      {/* Mini stats cards */}
      <div className="grid-3">
        <div className="glass-card" style={{ padding: '1.25rem 1.5rem', display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <div style={{ background: 'rgba(59, 130, 246, 0.15)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '10px' }}>
            <Calendar size={22} />
          </div>
          <div>
            <h3 style={{ margin: 0, fontSize: '1.5rem' }}>{total}</h3>
            <p style={{ margin: 0, fontSize: '0.85rem' }}>Total Patients</p>
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
            <p style={{ margin: 0, fontSize: '0.85rem' }}>Confirmed Sessions</p>
          </div>
        </div>
      </div>

      {/* Alert boxes */}
      {error && (
        <div className="alert alert-error">
          <AlertCircle size={18} />
          <span>{error}</span>
        </div>
      )}
      {success && (
        <div className="alert alert-success">
          <CheckCircle size={18} />
          <span>{success}</span>
        </div>
      )}

      {/* Main Grid: Schedule Queue & Availability edit */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.8fr 1.2fr', gap: '2rem', alignItems: 'start' }}>
        
        {/* Appointments Queue */}
        <div className="glass-card" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Patient Appointments</h2>
          
          {loading ? (
            <p>Loading patient schedules...</p>
          ) : appointments.length === 0 ? (
            <p style={{ color: 'var(--text-muted)', fontStyle: 'italic' }}>No patient schedules found in database.</p>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th>Patient</th>
                    <th>Date & Time</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {appointments.map((appt) => (
                    <tr key={appt.id}>
                      <td>
                        <div style={{ fontWeight: 600, color: 'var(--text-main)' }}>{appt.patient_name}</div>
                        <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.patient_phone || appt.patient_email}</div>
                      </td>
                      <td>
                        <div>{appt.appointment_date}</div>
                        <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.appointment_time}</div>
                      </td>
                      <td style={{ maxWidth: '150px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {appt.notes || <span style={{ fontStyle: 'italic' }}>None</span>}
                      </td>
                      <td>
                        <span className={`badge badge-${appt.status}`}>{appt.status}</span>
                      </td>
                      <td>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                          {appt.status === 'pending' && (
                            <>
                              <button
                                className="btn btn-secondary"
                                style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                                onClick={() => handleStatusUpdate(appt.id, 'confirmed')}
                                disabled={actionLoading}
                              >
                                <Check size={14} /> Confirm
                              </button>
                              <button
                                className="btn btn-danger"
                                style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                                onClick={() => handleStatusUpdate(appt.id, 'cancelled')}
                                disabled={actionLoading}
                              >
                                <X size={14} /> Refuse
                              </button>
                            </>
                          )}
                          {appt.status === 'confirmed' && (
                            <>
                              <button
                                className="btn btn-primary"
                                style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                                onClick={() => handleStatusUpdate(appt.id, 'completed')}
                                disabled={actionLoading}
                              >
                                <Check size={14} /> Done
                              </button>
                              <button
                                className="btn btn-danger btn-outline"
                                style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem', boxShadow: 'none' }}
                                onClick={() => handleStatusUpdate(appt.id, 'cancelled')}
                                disabled={actionLoading}
                              >
                                <X size={14} /> Cancel
                              </button>
                            </>
                          )}
                          {(appt.status === 'completed' || appt.status === 'cancelled') && (
                            <span style={{ fontStyle: 'italic', fontSize: '0.8rem', color: 'var(--text-muted)' }}>Closed</span>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Change Availability Settings */}
        <div className="glass-card" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Schedule & Availability</h2>
          <p>Define your consulting availability terms below so that patients are informed before booking appointments.</p>
          
          <form onSubmit={handleUpdateAvailability} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div className="form-group">
              <label className="form-label">Availability Description</label>
              <textarea
                className="form-input"
                rows="4"
                placeholder="e.g. Monday - Thursday (9 AM - 1 PM)"
                style={{ resize: 'vertical' }}
                value={availability}
                onChange={(e) => setAvailability(e.target.value)}
                disabled={loading || availabilityLoading}
                required
              ></textarea>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              style={{ width: '100%' }}
              disabled={loading || availabilityLoading}
            >
              {availabilityLoading ? 'Saving changes...' : 'Update Availability'} <Clipboard size={16} />
            </button>
          </form>
        </div>

      </div>

    </div>
  );
}
