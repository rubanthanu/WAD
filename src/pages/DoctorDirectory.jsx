import React, { useState, useEffect, useContext } from 'react';
import { Link } from 'react-router-dom';
import { AuthContext } from '../App';
import { Search, Filter, Calendar, Mail, Phone, Bookmark } from 'lucide-react';

export default function DoctorDirectory() {
  const { apiFetch } = useContext(AuthContext);

  const [doctors, setDoctors] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [selectedDept, setSelectedDept] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchInitialData();
  }, []);

  const fetchInitialData = async () => {
    setLoading(true);
    setError('');
    try {
      // Fetch both doctors and departments in parallel
      const [doctorsData, deptsData] = await Promise.all([
        apiFetch('/doctors.php'),
        apiFetch('/departments.php')
      ]);

      setDoctors(doctorsData || []);
      setDepartments(deptsData || []);
    } catch (err) {
      setError('Failed to load directory. Make sure you booted the database.');
    } finally {
      setLoading(false);
    }
  };

  const filteredDoctors = doctors.filter((doc) => {
    const matchesDept = selectedDept === '' || doc.department_name === selectedDept;
    const matchesSearch = 
      doc.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      doc.specialization.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesDept && matchesSearch;
  });

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      
      {/* Title */}
      <div>
        <h1 style={{ fontSize: '2.2rem', margin: 0 }}>Meet Our Doctors</h1>
        <p>Search through our medical roster of specialists and check their active schedule availability.</p>
      </div>

      {/* Filter and Search Bar */}
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
            placeholder="Search by doctor name or specialty..."
            style={{ paddingLeft: '2.5rem' }}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        {/* Filter Dropdown */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', minWidth: '200px' }}>
          <Filter size={18} color="var(--primary)" />
          <select 
            className="form-input form-select"
            value={selectedDept}
            onChange={(e) => setSelectedDept(e.target.value)}
          >
            <option value="">All Departments</option>
            {departments.map((dept) => (
              <option key={dept.id} value={dept.name}>{dept.name}</option>
            ))}
          </select>
        </div>

      </div>

      {/* Roster Listing */}
      {loading ? (
        <div style={{ textAlign: 'center', padding: '3rem' }}>
          <p>Fetching doctor roster...</p>
        </div>
      ) : error ? (
        <div className="alert alert-error" style={{ margin: '0 auto', maxWidth: '600px', width: '100%' }}>
          {error}
        </div>
      ) : filteredDoctors.length === 0 ? (
        <div style={{ textAlign: 'center', padding: '3rem' }} className="glass-card">
          <p>No doctors found matching the filter criteria.</p>
        </div>
      ) : (
        <div className="grid-3">
          {filteredDoctors.map((doc) => (
            <div key={doc.id} className="glass-card" style={{
              display: 'flex',
              flexDirection: 'column',
              gap: '1.25rem',
              justifyContent: 'space-between',
              position: 'relative'
            }}>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                {/* Department Tag */}
                <span style={{
                  background: 'rgba(59, 130, 246, 0.1)',
                  color: 'var(--primary)',
                  padding: '0.25rem 0.75rem',
                  borderRadius: '50px',
                  fontSize: '0.75rem',
                  fontWeight: 600,
                  width: 'fit-content'
                }}>
                  {doc.department_name}
                </span>

                <h3 style={{ margin: 0, fontSize: '1.35rem' }}>{doc.name}</h3>
                <p style={{ color: 'var(--secondary)', fontWeight: 600, fontSize: '0.9rem', margin: 0 }}>
                  {doc.specialization}
                </p>

                <div style={{
                  borderTop: '1px solid var(--glass-border)',
                  marginTop: '0.5rem',
                  paddingTop: '0.75rem',
                  display: 'flex',
                  flexDirection: 'column',
                  gap: '0.5rem',
                  fontSize: '0.85rem'
                }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <Mail size={14} color="var(--text-muted)" />
                    <span>{doc.email}</span>
                  </div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                    <Bookmark size={14} color="var(--text-muted)" />
                    <span>Schedule: <strong>{doc.availability}</strong></span>
                  </div>
                </div>
              </div>

              <Link to="/login" className="btn btn-primary" style={{ width: '100%', fontSize: '0.85rem', padding: '0.5rem' }}>
                Book Appointment <Calendar size={16} />
              </Link>
            </div>
          ))}
        </div>
      )}

    </div>
  );
}
