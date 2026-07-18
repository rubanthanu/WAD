import React, { useState, useEffect, useContext } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { Calendar, ChevronLeft, AlertCircle, CheckCircle, Clock } from 'lucide-react';

export default function BookAppointment() {
  const { apiFetch } = useContext(AuthContext);
  const navigate = useNavigate();

  const [departments, setDepartments] = useState([]);
  const [doctors, setDoctors] = useState([]);
  
  // Selected fields
  const [selectedDept, setSelectedDept] = useState('');
  const [selectedDoctor, setSelectedDoctor] = useState('');
  const [date, setDate] = useState('');
  const [time, setTime] = useState('');
  const [notes, setNotes] = useState('');

  // States
  const [loading, setLoading] = useState(false);
  const [fetchDoctorsLoading, setFetchDoctorsLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Get departments on load
  useEffect(() => {
    fetchDepartments();
  }, []);

  // Fetch doctors when department changes
  useEffect(() => {
    if (selectedDept) {
      fetchDoctorsByDept(selectedDept);
    } else {
      setDoctors([]);
      setSelectedDoctor('');
    }
  }, [selectedDept]);

  const fetchDepartments = async () => {
    try {
      const depts = await apiFetch('/departments.php');
      setDepartments(depts || []);
    } catch (err) {
      setError('Failed to fetch departments.');
    }
  };

  const fetchDoctorsByDept = async (deptId) => {
    setFetchDoctorsLoading(true);
    setError('');
    try {
      const docs = await apiFetch(`/doctors.php?department_id=${deptId}`);
      setDoctors(docs || []);
      setSelectedDoctor('');
    } catch (err) {
      setError('Failed to fetch doctors for selected department.');
    } finally {
      setFetchDoctorsLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!selectedDoctor || !date || !time) {
      setError('Please fill in all required fields.');
      return;
    }

    const today = new Date().toISOString().split('T')[0];
    if (date < today) {
      setError('Cannot book appointments in the past.');
      return;
    }

    setLoading(true);
    try {
      const response = await apiFetch('/appointments.php', {
        method: 'POST',
        body: JSON.stringify({
          doctor_id: selectedDoctor,
          appointment_date: date,
          appointment_time: time,
          notes: notes
        })
      });

      if (response.success) {
        setSuccess('Appointment booked successfully! Redirecting...');
        setTimeout(() => {
          navigate('/patient');
        }, 1500);
      }
    } catch (err) {
      setError(err.message || 'Failed to book appointment. Check database settings.');
    } finally {
      setLoading(false);
    }
  };

  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const minDate = tomorrow.toISOString().split('T')[0];

  return (
    <div style={{ maxWidth: '600px', margin: '1rem auto 3rem auto', width: '100%' }}>
      <div style={{ marginBottom: '1.5rem' }}>
        <Link to="/patient" style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: '0.25rem',
          color: 'var(--text-muted)',
          textDecoration: 'none',
          fontSize: '0.9rem'
        }}>
          <ChevronLeft size={16} /> Back to Dashboard
        </Link>
      </div>

      <div className="glass-card" style={{ padding: '2.5rem' }}>
        <h1 style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>Book an Appointment</h1>
        <p style={{ marginBottom: '2.5rem' }}>Choose your clinical department, preferred practitioner, and choose a schedule.</p>

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

        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
          
          {/* 1. Department */}
          <div className="form-group">
            <label className="form-label">1. Medical Department *</label>
            <select 
              className="form-input form-select"
              value={selectedDept}
              onChange={(e) => setSelectedDept(e.target.value)}
              disabled={loading}
              required
            >
              <option value="">Select Department</option>
              {departments.map((dept) => (
                <option key={dept.id} value={dept.id}>{dept.name}</option>
              ))}
            </select>
          </div>

          {/* 2. Doctor */}
          <div className="form-group">
            <label className="form-label">2. Healthcare Specialist *</label>
            <select 
              className="form-input form-select"
              value={selectedDoctor}
              onChange={(e) => setSelectedDoctor(e.target.value)}
              disabled={loading || !selectedDept || fetchDoctorsLoading}
              required
            >
              <option value="">
                {fetchDoctorsLoading ? 'Loading Specialists...' : !selectedDept ? 'Select Department First' : 'Select Doctor'}
              </option>
              {doctors.map((doc) => (
                <option key={doc.id} value={doc.id}>
                  {doc.name} ({doc.specialization})
                </option>
              ))}
            </select>
            {selectedDoctor && doctors.length > 0 && (
              <div style={{
                marginTop: '0.5rem',
                fontSize: '0.85rem',
                color: 'var(--secondary)',
                display: 'flex',
                alignItems: 'center',
                gap: '0.25rem'
              }}>
                <Clock size={14} /> Doctor Availability: {doctors.find(d => d.id === parseInt(selectedDoctor))?.availability}
              </div>
            )}
          </div>

          {/* 3. Date & Time */}
          <div className="grid-2" style={{ gap: '1.25rem' }}>
            <div className="form-group">
              <label className="form-label">3. Date *</label>
              <input 
                type="date" 
                className="form-input"
                min={minDate}
                value={date}
                onChange={(e) => setDate(e.target.value)}
                disabled={loading}
                required
              />
            </div>
            
            <div className="form-group">
              <label className="form-label">4. Preferred Time *</label>
              <select 
                className="form-input form-select"
                value={time}
                onChange={(e) => setTime(e.target.value)}
                disabled={loading}
                required
              >
                <option value="">Select Time Slot</option>
                <option value="09:00:00">09:00 AM</option>
                <option value="09:30:00">09:30 AM</option>
                <option value="10:00:00">10:00 AM</option>
                <option value="10:30:00">10:30 AM</option>
                <option value="11:00:00">11:00 AM</option>
                <option value="11:30:00">11:30 AM</option>
                <option value="12:00:00">12:00 PM</option>
                <option value="13:30:00">01:30 PM</option>
                <option value="14:00:00">02:00 PM</option>
                <option value="14:30:00">02:40 PM</option>
                <option value="15:00:00">03:00 PM</option>
                <option value="15:30:00">03:30 PM</option>
                <option value="16:00:00">04:00 PM</option>
                <option value="16:30:00">04:30 PM</option>
              </select>
            </div>
          </div>

          {/* 4. Notes */}
          <div className="form-group">
            <label className="form-label">5. Appointment Notes (Symptoms, history, etc.)</label>
            <textarea 
              className="form-input" 
              rows="4" 
              placeholder="Provide context for the physician..."
              style={{ resize: 'vertical' }}
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              disabled={loading}
            ></textarea>
          </div>

          <button 
            type="submit" 
            className="btn btn-primary" 
            style={{ width: '100%', marginTop: '1rem' }}
            disabled={loading}
          >
            {loading ? 'Booking Appointment...' : 'Submit Booking'} <Calendar size={18} />
          </button>
        </form>
      </div>
    </div>
  );
}
