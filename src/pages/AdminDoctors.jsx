import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { ChevronLeft, Plus, Edit, Trash, AlertCircle, CheckCircle, HelpCircle } from 'lucide-react';

export default function AdminDoctors() {
  const { apiFetch } = useContext(AuthContext);

  const [doctors, setDoctors] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Mode: 'create' or 'edit'
  const [mode, setMode] = useState('create');
  const [editingDoctorId, setEditingDoctorId] = useState(null);

  // Form inputs
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [phone, setPhone] = useState('');
  const [specialization, setSpecialization] = useState('');
  const [departmentId, setDepartmentId] = useState('');
  const [availability, setAvailability] = useState('Monday - Friday (9 AM - 5 PM)');

  useEffect(() => {
    fetchInitialData();
  }, []);

  const fetchInitialData = async () => {
    setLoading(true);
    setError('');
    try {
      const [doctorsList, deptsList] = await Promise.all([
        apiFetch('/doctors.php'),
        apiFetch('/departments.php')
      ]);
      setDoctors(doctorsList || []);
      setDepartments(deptsList || []);
    } catch (err) {
      setError('Failed to fetch doctor roster or department list.');
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setMode('create');
    setEditingDoctorId(null);
    setName('');
    setEmail('');
    setPassword('');
    setPhone('');
    setSpecialization('');
    setDepartmentId('');
    setAvailability('Monday - Friday (9 AM - 5 PM)');
  };

  const handleEditClick = (doc) => {
    setMode('edit');
    setEditingDoctorId(doc.id);
    setName(doc.name);
    setEmail(doc.email);
    setPassword(''); // Don't show password hashes
    setPhone(doc.phone || '');
    setSpecialization(doc.specialization);
    // Find matching department id from department name
    const matchDept = departments.find(d => d.name === doc.department_name);
    setDepartmentId(matchDept ? matchDept.id : '');
    setAvailability(doc.availability || '');
    setError('');
    setSuccess('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (mode === 'create' && (!name || !email || !password || !specialization || !departmentId)) {
      setError('Please fill in all required fields (Name, Email, Password, Specialization, Department).');
      return;
    }

    if (mode === 'edit' && (!name || !email || !specialization || !departmentId)) {
      setError('Please fill in all required fields.');
      return;
    }

    setActionLoading(true);
    try {
      if (mode === 'create') {
        const result = await apiFetch('/doctors.php', {
          method: 'POST',
          body: JSON.stringify({
            name, email, password, phone, specialization, department_id: departmentId, availability
          })
        });
        if (result.success) {
          setSuccess('Doctor record created successfully!');
          resetForm();
          fetchInitialData();
        }
      } else {
        const result = await apiFetch('/doctors.php', {
          method: 'PUT',
          body: JSON.stringify({
            doctor_id: editingDoctorId,
            name, email, phone, specialization, department_id: departmentId, availability
          })
        });
        if (result.success) {
          setSuccess('Doctor profile updated successfully!');
          resetForm();
          fetchInitialData();
        }
      }
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Action failed.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleDelete = async (docId) => {
    if (!window.confirm('Deleting this doctor will remove their patient logs and login details. Are you sure?')) return;

    setError('');
    setSuccess('');
    setActionLoading(true);
    try {
      await apiFetch(`/doctors.php?id=${docId}`, {
        method: 'DELETE'
      });
      setSuccess('Doctor removed successfully.');
      fetchInitialData();
      resetForm();
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Deletion failed.');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Header */}
      <div>
        <div style={{ marginBottom: '1rem' }}>
          <Link to="/admin" style={{ display: 'inline-flex', alignItems: 'center', gap: '0.25rem', color: 'var(--text-muted)', textDecoration: 'none', fontSize: '0.9rem' }}>
            <ChevronLeft size={16} /> Back to Dashboard
          </Link>
        </div>
        <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Doctor Management</h1>
        <p>Register new medical professionals, change profiles, or remove entries.</p>
      </div>

      {/* Alert alerts */}
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

      {/* Main Grid: Form on Left, Table on Right */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.1fr 1.9fr', gap: '2rem', alignItems: 'start' }}>
        
        {/* CRUD Form */}
        <div className="glass-card">
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>
            {mode === 'create' ? 'Add Doctor' : 'Edit Doctor'}
          </h2>

          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <div className="form-group">
              <label className="form-label">Full Name *</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="Dr. John Watson" 
                value={name} 
                onChange={(e) => setName(e.target.value)}
                disabled={actionLoading}
                required
              />
            </div>

            <div className="form-group">
              <label className="form-label">Email Address *</label>
              <input 
                type="email" 
                className="form-input" 
                placeholder="john.watson@hospital.com" 
                value={email} 
                onChange={(e) => setEmail(e.target.value)}
                disabled={actionLoading}
                required
              />
            </div>

            {mode === 'create' && (
              <div className="form-group">
                <label className="form-label">Password *</label>
                <input 
                  type="password" 
                  className="form-input" 
                  placeholder="Password for doctor login" 
                  value={password} 
                  onChange={(e) => setPassword(e.target.value)}
                  disabled={actionLoading}
                  required
                />
              </div>
            )}

            <div className="form-group">
              <label className="form-label">Phone Number</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="555-0155" 
                value={phone} 
                onChange={(e) => setPhone(e.target.value)}
                disabled={actionLoading}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Specialization (e.g. Cardiologist) *</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="Neurologist" 
                value={specialization} 
                onChange={(e) => setSpecialization(e.target.value)}
                disabled={actionLoading}
                required
              />
            </div>

            <div className="form-group">
              <label className="form-label">Clinical Department *</label>
              <select 
                className="form-input form-select"
                value={departmentId}
                onChange={(e) => setDepartmentId(e.target.value)}
                disabled={actionLoading}
                required
              >
                <option value="">Select Department</option>
                {departments.map((dept) => (
                  <option key={dept.id} value={dept.id}>{dept.name}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label className="form-label">Availability Schedule Description</label>
              <textarea 
                className="form-input" 
                rows="2"
                placeholder="e.g. Mon, Wed, Fri (9 AM - 1 PM)"
                style={{ resize: 'vertical' }}
                value={availability}
                onChange={(e) => setAvailability(e.target.value)}
                disabled={actionLoading}
              ></textarea>
            </div>

            <div style={{ display: 'flex', gap: '0.75rem', marginTop: '1rem' }}>
              <button type="submit" className="btn btn-primary" style={{ flex: 1 }} disabled={actionLoading}>
                {mode === 'create' ? 'Create Doctor' : 'Save Changes'}
              </button>
              {mode === 'edit' && (
                <button type="button" className="btn btn-outline" onClick={resetForm} disabled={actionLoading}>
                  Cancel
                </button>
              )}
            </div>

          </form>
        </div>

        {/* Doctors Table */}
        <div className="glass-card" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Registered Physicians</h2>
          
          {loading ? (
            <p>Loading roster...</p>
          ) : doctors.length === 0 ? (
            <p style={{ fontStyle: 'italic' }}>No doctors found.</p>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th>Doctor</th>
                    <th>Department / Specialization</th>
                    <th>Availability</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {doctors.map((doc) => (
                    <tr key={doc.id}>
                      <td>
                        <div style={{ fontWeight: 600, color: 'white' }}>{doc.name}</div>
                        <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{doc.email}</div>
                      </td>
                      <td>
                        <div>{doc.department_name}</div>
                        <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{doc.specialization}</div>
                      </td>
                      <td style={{ fontSize: '0.85rem' }}>{doc.availability}</td>
                      <td>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                          <button
                            className="btn btn-outline"
                            style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                            onClick={() => handleEditClick(doc)}
                            disabled={actionLoading}
                          >
                            <Edit size={12} /> Edit
                          </button>
                          <button
                            className="btn btn-danger"
                            style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                            onClick={() => handleDelete(doc.id)}
                            disabled={actionLoading}
                          >
                            <Trash size={12} /> Remove
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

      </div>

    </div>
  );
}
