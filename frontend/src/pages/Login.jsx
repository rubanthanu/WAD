import React, { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../App';
import { LogIn, UserPlus, AlertCircle, CheckCircle } from 'lucide-react';

export default function Login() {
  const { login, register } = useContext(AuthContext);
  const navigate = useNavigate();

  const [isLoginTab, setIsLoginTab] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  // Form states
  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  const [registerName, setRegisterName] = useState('');
  const [registerEmail, setRegisterEmail] = useState('');
  const [registerPhone, setRegisterPhone] = useState('');
  const [registerDob, setRegisterDob] = useState('');
  const [registerGender, setRegisterGender] = useState('male');
  const [registerPassword, setRegisterPassword] = useState('');
  const [registerConfirmPassword, setRegisterConfirmPassword] = useState('');

  // Form handling
  const handleLoginSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Client-side validations
    if (!loginEmail || !loginPassword) {
      setError('Please fill in all fields.');
      return;
    }

    setLoading(true);
    try {
      const result = await login(loginEmail, loginPassword);
      if (result.success) {
        setSuccess('Login successful! Redirecting...');
        setTimeout(() => {
          // Redirect based on role
          if (result.user.role === 'admin') navigate('/admin');
          else if (result.user.role === 'doctor') navigate('/doctor');
          else navigate('/patient');
        }, 1000);
      }
    } catch (err) {
      setError(err.message || 'Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  const handleRegisterSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Client-side validations
    if (!registerName || !registerEmail || !registerPassword || !registerConfirmPassword) {
      setError('Name, email, and password fields are required.');
      return;
    }

    if (registerPassword.length < 6) {
      setError('Password must be at least 6 characters long.');
      return;
    }

    if (registerPassword !== registerConfirmPassword) {
      setError('Passwords do not match.');
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(registerEmail)) {
      setError('Please enter a valid email address.');
      return;
    }

    setLoading(true);
    try {
      const result = await register(
        registerName, 
        registerEmail, 
        registerPassword, 
        registerPhone,
        registerDob,
        registerGender
      );
      if (result.success) {
        setSuccess('Registration successful! You can now login.');
        // Clear registration fields
        setRegisterName('');
        setRegisterEmail('');
        setRegisterPhone('');
        setRegisterDob('');
        setRegisterGender('male');
        setRegisterPassword('');
        setRegisterConfirmPassword('');
        // Switch to login tab
        setTimeout(() => {
          setIsLoginTab(true);
          setSuccess('');
        }, 2000);
      }
    } catch (err) {
      setError(err.message || 'Registration failed.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{
      maxWidth: '450px',
      margin: '2rem auto',
      width: '100%'
    }}>
      <div className="glass-card" style={{ padding: '2.5rem 2rem' }}>

        {/* Tab Headers */}
        <div style={{
          display: 'flex',
          borderBottom: '1px solid var(--glass-border)',
          marginBottom: '2rem',
          position: 'relative'
        }}>
          <button
            onClick={() => { setIsLoginTab(true); setError(''); setSuccess(''); }}
            style={{
              flex: 1,
              background: 'none',
              border: 'none',
              padding: '1rem',
              color: isLoginTab ? 'var(--primary)' : 'var(--text-muted)',
              fontSize: '1.1rem',
              fontWeight: '600',
              cursor: 'pointer',
              borderBottom: isLoginTab ? '2px solid var(--primary)' : 'none',
              transition: 'var(--transition-fast)'
            }}
          >
            Sign In
          </button>
          <button
            onClick={() => { setIsLoginTab(false); setError(''); setSuccess(''); }}
            style={{
              flex: 1,
              background: 'none',
              border: 'none',
              padding: '1rem',
              color: !isLoginTab ? 'var(--primary)' : 'var(--text-muted)',
              fontSize: '1.1rem',
              fontWeight: '600',
              cursor: 'pointer',
              borderBottom: !isLoginTab ? '2px solid var(--primary)' : 'none',
              transition: 'var(--transition-fast)'
            }}
          >
            Register
          </button>
        </div>

        {/* Notifications */}
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

        {isLoginTab ? (
          /* LOGIN FORM */
          <form onSubmit={handleLoginSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div className="form-group">
              <label className="form-label">Email Address</label>
              <input
                type="email"
                className="form-input"
                placeholder="you@example.com"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                disabled={loading}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Password</label>
              <input
                type="password"
                className="form-input"
                placeholder="••••••••"
                value={loginPassword}
                onChange={(e) => setLoginPassword(e.target.value)}
                disabled={loading}
              />
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              style={{ width: '100%', marginTop: '1rem' }}
              disabled={loading}
            >
              {loading ? 'Processing...' : 'Sign In'} <LogIn size={18} />
            </button>

            <div style={{ textAlign: 'center', marginTop: '1rem', fontSize: '0.85rem', color: 'var(--text-muted)' }}>
              Demo Credentials:<br />
              Admin: <strong>admin@hospital.com</strong> (PW: <strong>admin123</strong>)<br />
              Doctor: <strong>sarah.j@hospital.com</strong> (PW: <strong>doctor123</strong>)<br />
              Patient: <strong>jane.doe@example.com</strong> (PW: <strong>patient123</strong>)
            </div>
          </form>
        ) : (
          /* REGISTRATION FORM */
          <form onSubmit={handleRegisterSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.1rem' }}>
            <div className="form-group">
              <label className="form-label">Full Name</label>
              <input
                type="text"
                className="form-input"
                placeholder="Jane Doe"
                value={registerName}
                onChange={(e) => setRegisterName(e.target.value)}
                disabled={loading}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Email Address</label>
              <input
                type="email"
                className="form-input"
                placeholder="jane.doe@example.com"
                value={registerEmail}
                onChange={(e) => setRegisterEmail(e.target.value)}
                disabled={loading}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Phone Number (Optional)</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="555-0199"
                value={registerPhone}
                onChange={(e) => setRegisterPhone(e.target.value)}
                disabled={loading}
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
              <div className="form-group">
                <label className="form-label">Date of Birth</label>
                <input 
                  type="date" 
                  className="form-input" 
                  value={registerDob}
                  onChange={(e) => setRegisterDob(e.target.value)}
                  disabled={loading}
                />
              </div>

              <div className="form-group">
                <label className="form-label">Gender</label>
                <select 
                  className="form-input" 
                  value={registerGender}
                  onChange={(e) => setRegisterGender(e.target.value)}
                  disabled={loading}
                >
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <div className="form-group">
              <label className="form-label">Password</label>
              <input
                type="password"
                className="form-input"
                placeholder="At least 6 characters"
                value={registerPassword}
                onChange={(e) => setRegisterPassword(e.target.value)}
                disabled={loading}
              />
            </div>

            <div className="form-group">
              <label className="form-label">Confirm Password</label>
              <input
                type="password"
                className="form-input"
                placeholder="Repeat password"
                value={registerConfirmPassword}
                onChange={(e) => setRegisterConfirmPassword(e.target.value)}
                disabled={loading}
              />
            </div>

            <button
              type="submit"
              className="btn btn-secondary"
              style={{ width: '100%', marginTop: '1rem' }}
              disabled={loading}
            >
              {loading ? 'Creating Account...' : 'Register'} <UserPlus size={18} />
            </button>
          </form>
        )}

      </div>
    </div>
  );
}
