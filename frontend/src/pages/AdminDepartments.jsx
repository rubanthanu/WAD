import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { ChevronLeft, Plus, Edit, Trash, AlertCircle, CheckCircle } from 'lucide-react';

export default function AdminDepartments() {
  const { apiFetch } = useContext(AuthContext);

  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Form states
  const [mode, setMode] = useState('create');
  const [editingDeptId, setEditingDeptId] = useState(null);
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');

  useEffect(() => {
    fetchDepartments();
  }, []);

  const fetchDepartments = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await apiFetch('/departments.php');
      setDepartments(data || []);
    } catch (err) {
      setError('Failed to fetch department roster.');
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setMode('create');
    setEditingDeptId(null);
    setName('');
    setDescription('');
  };

  const handleEditClick = (dept) => {
    setMode('edit');
    setEditingDeptId(dept.id);
    setName(dept.name);
    setDescription(dept.description || '');
    setError('');
    setSuccess('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!name) {
      setError('Department name is required.');
      return;
    }

    setActionLoading(true);
    try {
      if (mode === 'create') {
        const result = await apiFetch('/departments.php', {
          method: 'POST',
          body: JSON.stringify({ name, description })
        });
        if (result.success) {
          setSuccess('Department created successfully!');
          resetForm();
          fetchDepartments();
        }
      } else {
        const result = await apiFetch('/departments.php', {
          method: 'PUT',
          body: JSON.stringify({ id: editingDeptId, name, description })
        });
        if (result.success) {
          setSuccess('Department details updated!');
          resetForm();
          fetchDepartments();
        }
      }
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Action failed.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleDelete = async (deptId) => {
    if (!window.confirm('Are you sure you want to delete this clinical department?')) return;

    setError('');
    setSuccess('');
    setActionLoading(true);
    try {
      const result = await apiFetch(`/departments.php?id=${deptId}`, {
        method: 'DELETE'
      });
      if (result.success) {
        setSuccess('Department removed successfully.');
        fetchDepartments();
        resetForm();
      }
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.message || 'Cannot delete department. Check if there are active doctors assigned to it.');
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
        <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Department Management</h1>
        <p>Overview department definitions, modify clinical names, or manage listings.</p>
      </div>

      {/* alerts */}
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

      {/* Grid: Edit left, table right */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.1fr 1.9fr', gap: '2rem', alignItems: 'start' }}>
        
        {/* Form panel */}
        <div className="glass-card">
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>
            {mode === 'create' ? 'Create Department' : 'Edit Department'}
          </h2>

          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div className="form-group">
              <label className="form-label">Department Name *</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="Cardiology" 
                value={name} 
                onChange={(e) => setName(e.target.value)}
                disabled={actionLoading}
                required
              />
            </div>

            <div className="form-group">
              <label className="form-label">Description</label>
              <textarea 
                className="form-input" 
                rows="4" 
                placeholder="Details about the department scope..." 
                style={{ resize: 'vertical' }}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                disabled={actionLoading}
              ></textarea>
            </div>

            <div style={{ display: 'flex', gap: '0.75rem', marginTop: '1rem' }}>
              <button type="submit" className="btn btn-primary" style={{ flex: 1 }} disabled={actionLoading}>
                {mode === 'create' ? 'Create' : 'Save Changes'}
              </button>
              {mode === 'edit' && (
                <button type="button" className="btn btn-outline" onClick={resetForm} disabled={actionLoading}>
                  Cancel
                </button>
              )}
            </div>
          </form>
        </div>

        {/* Table panel */}
        <div className="glass-card" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Departments list</h2>
          
          {loading ? (
            <p>Loading departments...</p>
          ) : departments.length === 0 ? (
            <p style={{ fontStyle: 'italic' }}>No departments found.</p>
          ) : (
            <div className="table-container">
              <table className="custom-table">
                <thead>
                  <tr>
                    <th style={{ width: '200px' }}>Name</th>
                    <th>Description</th>
                    <th style={{ width: '150px' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {departments.map((dept) => (
                    <tr key={dept.id}>
                      <td style={{ fontWeight: 600, color: 'var(--text-main)' }}>{dept.name}</td>
                      <td>{dept.description || <span style={{ fontStyle: 'italic' }}>No description</span>}</td>
                      <td>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                          <button
                            className="btn btn-outline"
                            style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                            onClick={() => handleEditClick(dept)}
                            disabled={actionLoading}
                          >
                            <Edit size={12} /> Edit
                          </button>
                          <button
                            className="btn btn-danger"
                            style={{ padding: '0.35rem 0.5rem', fontSize: '0.75rem' }}
                            onClick={() => handleDelete(dept.id)}
                            disabled={actionLoading}
                          >
                            <Trash size={12} /> Delete
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
