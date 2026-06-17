import React, { useState, useEffect, useContext } from 'react';
import { Link as RouterLink } from 'react-router-dom';
import { AuthContext } from '../App';
import { ChevronLeft, Search, Filter, Trash2, Edit2, AlertCircle, CheckCircle, RefreshCw } from 'lucide-react';

export default function AdminAppointments() {
  const { apiFetch } = useContext(AuthContext);

  const [appointments, setAppointments] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    fetchAppointments();
  }, [searchTerm, selectedStatus]);

  const fetchAppointments = async () => {
    setLoading(true);
    setError('');
    try {
      const query = `/appointments.php?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(selectedStatus)}`;
      const data = await apiFetch(query);
      setAppointments(data || []);
    } catch (err) {
      setError('Failed to fetch appointments list.');
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = async (apptId, newStatus) => {
    setError('');
    setSuccess('');
    setActionLoading(true);
    try {
      await apiFetch('/appointments.php', {
        method: 'PUT',
        body: JSON.stringify({
          appointment_id: apptId,
          status: newStatus
        })
      });
      setSuccess('Appointment status updated successfully.');
      fetchAppointments();
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Failed to update status.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleDelete = async (apptId) => {
    if (!window.confirm('Are you absolutely sure you want to delete this appointment from the system?')) return;

    setError('');
    setSuccess('');
    setActionLoading(true);
    try {
      await apiFetch(`/appointments.php?id=${apptId}`, {
        method: 'DELETE'
      });
      setSuccess('Appointment deleted successfully.');
      fetchAppointments();
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Failed to delete appointment.');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Header */}
      <div>
        <div style={{ marginBottom: '1rem' }}>
          <RouterLink to="/admin" style={{ display: 'inline-flex', alignItems: 'center', gap: '0.25rem', color: 'var(--text-muted)', textDecoration: 'none', fontSize: '0.9rem' }}>
            <ChevronLeft size={16} /> Back to Dashboard
          </RouterLink>
        </div>
        <h1 style={{ fontSize: '2.2rem', margin: 0 }}>System Appointments</h1>
        <p>Overview and manage all scheduled medical visits across the clinic.</p>
      </div>

      {/* Alert panels */}
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

      {/* Filter bar */}
      <div className="glass-card" style={{
        display: 'flex',
        gap: '1.5rem',
        padding: '1.25rem 1.5rem',
        flexWrap: 'wrap',
        alignItems: 'center'
      }}>
        
        {/* Search */}
        <div style={{ flex: 1, position: 'relative', minWidth: '250px' }}>
          <Search size={18} style={{
            position: 'absolute',
            left: '1rem',
            top: '50%',
            transform: 'translateY(-50%)',
            color: 'var(--text-muted)'
          }} />
          <input 
            type="text" 
            className="form-input" 
            placeholder="Search by Patient, Doctor, or Department..."
            style={{ paddingLeft: '2.5rem' }}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        {/* Status Dropdown */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', minWidth: '200px' }}>
          <Filter size={18} color="var(--primary)" />
          <select 
            className="form-input form-select"
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
          </select>
        </div>

      </div>

      {/* Master appointments table */}
      <div className="glass-card">
        {loading ? (
          <p>Fetching appointments queue...</p>
        ) : appointments.length === 0 ? (
          <p style={{ fontStyle: 'italic', textAlign: 'center', padding: '2rem' }}>No appointments match the filter criteria.</p>
        ) : (
          <div className="table-container">
            <table className="custom-table">
              <thead>
                <tr>
                  <th>Patient</th>
                  <th>Doctor / Specialty</th>
                  <th>Department</th>
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
                      <div style={{ fontWeight: 600, color: 'white' }}>{appt.patient_name}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.patient_phone || appt.patient_email}</div>
                    </td>
                    <td>
                      <div style={{ fontWeight: 600, color: 'white' }}>{appt.doctor_name}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.specialization}</div>
                    </td>
                    <td>{appt.department_name}</td>
                    <td>
                      <div>{appt.appointment_date}</div>
                      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{appt.appointment_time}</div>
                    </td>
                    <td style={{ maxWidth: '150px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      {appt.notes || <span style={{ fontStyle: 'italic' }}>None</span>}
                    </td>
                    <td>
                      <select
                        className="form-input"
                        style={{
                          padding: '0.25rem 0.5rem',
                          fontSize: '0.85rem',
                          width: '120px',
                          background: 'rgba(255,255,255,0.05)'
                        }}
                        value={appt.status}
                        onChange={(e) => handleStatusChange(appt.id, e.target.value)}
                        disabled={actionLoading}
                      >
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                      </select>
                    </td>
                    <td>
                      <button
                        className="btn btn-danger"
                        style={{
                          padding: '0.35rem 0.6rem',
                          fontSize: '0.8rem',
                          display: 'inline-flex',
                          alignItems: 'center',
                          gap: '0.25rem'
                        }}
                        onClick={() => handleDelete(appt.id)}
                        disabled={actionLoading}
                      >
                        <Trash2 size={14} /> Delete
                      </button>
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
