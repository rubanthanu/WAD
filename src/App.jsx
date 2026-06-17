import React, { createContext, useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Link, Navigate, useNavigate, useLocation } from 'react-router-dom';
import { LogOut, Menu, X, ShieldAlert, Heart, Calendar } from 'lucide-react';

// Import all pages
import Home from './pages/Home';
import Login from './pages/Login';
import Contact from './pages/Contact';
import DoctorDirectory from './pages/DoctorDirectory';
import PatientDashboard from './pages/PatientDashboard';
import BookAppointment from './pages/BookAppointment';
import DoctorDashboard from './pages/DoctorDashboard';
import AdminDashboard from './pages/AdminDashboard';
import AdminAppointments from './pages/AdminAppointments';
import AdminDoctors from './pages/AdminDoctors';
import AdminDepartments from './pages/AdminDepartments';

// Create Auth Context
export const AuthContext = createContext();

export default function App() {
  const [user, setUser] = useState(null);
  const [checkingAuth, setCheckingAuth] = useState(true);

  useEffect(() => {
    // Check if user is cached in local storage
    const cachedUser = localStorage.getItem('hospital_user');
    if (cachedUser) {
      setUser(JSON.parse(cachedUser));
    }
    setCheckingAuth(false);
  }, []);

  // Custom HTTP fetch client for communicating with the PHP JSON API
  const apiFetch = async (url, options = {}) => {
    const headers = {
      ...options.headers,
    };
    
    // Auto-detect JSON and configure content-type
    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }

    // Pass custom authentication headers to solve CORS session variables in development
    const cachedUser = localStorage.getItem('hospital_user');
    if (cachedUser) {
      const parsed = JSON.parse(cachedUser);
      headers['X-User-Id'] = parsed.id;
      headers['X-User-Role'] = parsed.role;
    }

    try {
      const response = await fetch(`http://localhost/WAD/api${url}`, {
        ...options,
        headers,
      });

      const text = await response.text();
      let data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (e) {
        throw new Error("API returned invalid JSON: " + text);
      }

      if (!response.ok) {
        throw new Error(data.error || `Request failed with code ${response.status}`);
      }
      return data;
    } catch (err) {
      console.error("Fetch API error:", err);
      throw err;
    }
  };

  const login = async (email, password) => {
    try {
      const data = await apiFetch('/auth.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'login', email, password })
      });
      if (data.success && data.user) {
        setUser(data.user);
        localStorage.setItem('hospital_user', JSON.stringify(data.user));
        return { success: true, user: data.user };
      }
      throw new Error("Invalid server credentials response.");
    } catch (err) {
      throw err;
    }
  };

  const register = async (name, email, password, phone) => {
    try {
      const data = await apiFetch('/auth.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'register', name, email, password, phone })
      });
      if (data.success) {
        return { success: true };
      }
      throw new Error(data.error || "Failed to register.");
    } catch (err) {
      throw err;
    }
  };

  const logout = async () => {
    try {
      await apiFetch('/auth.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'logout' })
      });
    } catch (e) {
      console.warn("Server session logout error: ", e);
    }
    setUser(null);
    localStorage.removeItem('hospital_user');
  };

  if (checkingAuth) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '100vh', background: 'var(--bg-main)' }}>
        <p>Loading application state...</p>
      </div>
    );
  }

  return (
    <AuthContext.Provider value={{ user, login, register, logout, apiFetch }}>
      <BrowserRouter>
        <AppLayout>
          <Routes>
            {/* Public routes */}
            <Route path="/" element={<Home />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/doctors" element={<DoctorDirectory />} />
            <Route path="/login" element={user ? <RoleRedirect user={user} /> : <Login />} />

            {/* Protected Patient Routes */}
            <Route 
              path="/patient" 
              element={
                <ProtectedRoute allowedRoles={['patient']} user={user}>
                  <PatientDashboard />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/patient/book" 
              element={
                <ProtectedRoute allowedRoles={['patient']} user={user}>
                  <BookAppointment />
                </ProtectedRoute>
              } 
            />

            {/* Protected Doctor Routes */}
            <Route 
              path="/doctor" 
              element={
                <ProtectedRoute allowedRoles={['doctor']} user={user}>
                  <DoctorDashboard />
                </ProtectedRoute>
              } 
            />

            {/* Protected Admin Routes */}
            <Route 
              path="/admin" 
              element={
                <ProtectedRoute allowedRoles={['admin']} user={user}>
                  <AdminDashboard />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/admin/appointments" 
              element={
                <ProtectedRoute allowedRoles={['admin']} user={user}>
                  <AdminAppointments />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/admin/doctors" 
              element={
                <ProtectedRoute allowedRoles={['admin']} user={user}>
                  <AdminDoctors />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/admin/departments" 
              element={
                <ProtectedRoute allowedRoles={['admin']} user={user}>
                  <AdminDepartments />
                </ProtectedRoute>
              } 
            />

            {/* Catch-all fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </AppLayout>
      </BrowserRouter>
    </AuthContext.Provider>
  );
}

// Redirects user from Login page if they are already logged in
function RoleRedirect({ user }) {
  if (user.role === 'admin') return <Navigate to="/admin" replace />;
  if (user.role === 'doctor') return <Navigate to="/doctor" replace />;
  return <Navigate to="/patient" replace />;
}

// Guard components for protected routes
function ProtectedRoute({ children, allowedRoles, user }) {
  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (!allowedRoles.includes(user.role)) {
    return (
      <div className="glass-card" style={{ maxWidth: '500px', margin: '4rem auto', textAlign: 'center', display: 'flex', flexDirection: 'column', gap: '1.5rem', alignItems: 'center' }}>
        <ShieldAlert size={48} color="var(--danger)" />
        <h2 style={{ margin: 0 }}>Permission Denied</h2>
        <p>You do not have administrative clearance to access this portal page.</p>
        <Link to="/" className="btn btn-primary">Return Home</Link>
      </div>
    );
  }

  return children;
}

// Shell layout: Header navigation and footer details
function AppLayout({ children }) {
  const { user, logout } = React.useContext(AuthContext);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();

  // Close mobile menu on path changes
  useEffect(() => {
    setMobileMenuOpen(false);
  }, [location.pathname]);

  const handleLogoutClick = () => {
    logout();
    navigate('/');
  };

  return (
    <div className="app-container">
      {/* Header Navbar */}
      <header style={{
        background: 'rgba(9, 13, 22, 0.8)',
        backdropFilter: 'blur(12px)',
        borderBottom: '1px solid var(--glass-border)',
        position: 'sticky',
        top: 0,
        zIndex: 100
      }}>
        <div style={{
          maxWidth: '1200px',
          margin: '0 auto',
          padding: '1rem',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}>
          {/* Logo */}
          <Link to="/" style={{
            display: 'flex',
            alignItems: 'center',
            gap: '0.5rem',
            textDecoration: 'none',
            color: 'white',
            fontWeight: 800,
            fontSize: '1.25rem'
          }}>
            <Heart size={22} color="var(--primary)" fill="var(--primary)" />
            <span>Antigravity <span style={{ color: 'var(--primary)' }}>Clinic</span></span>
          </Link>

          {/* Navigation Links - Desktop */}
          <nav className="desktop-nav" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
            {/* Common Public Links */}
            <Link to="/" className={`nav-link ${location.pathname === '/' ? 'active' : ''}`}>Home</Link>
            <Link to="/doctors" className={`nav-link ${location.pathname === '/doctors' ? 'active' : ''}`}>Doctors</Link>
            <Link to="/contact" className={`nav-link ${location.pathname === '/contact' ? 'active' : ''}`}>Contact</Link>

            {/* Role-Specific Navigation links */}
            {user?.role === 'patient' && (
              <>
                <Link to="/patient" className={`nav-link ${location.pathname === '/patient' ? 'active' : ''}`}>Dashboard</Link>
                <Link to="/patient/book" className={`nav-link ${location.pathname === '/patient/book' ? 'active' : ''}`}>Book Appointment</Link>
              </>
            )}

            {user?.role === 'doctor' && (
              <Link to="/doctor" className={`nav-link ${location.pathname === '/doctor' ? 'active' : ''}`}>Consultations</Link>
            )}

            {user?.role === 'admin' && (
              <>
                <Link to="/admin" className={`nav-link ${location.pathname === '/admin' ? 'active' : ''}`}>Control Panel</Link>
                <Link to="/admin/appointments" className={`nav-link ${location.pathname === '/admin/appointments' ? 'active' : ''}`}>Appointments</Link>
                <Link to="/admin/doctors" className={`nav-link ${location.pathname === '/admin/doctors' ? 'active' : ''}`}>Roster</Link>
                <Link to="/admin/departments" className={`nav-link ${location.pathname === '/admin/departments' ? 'active' : ''}`}>Depts</Link>
              </>
            )}

            {user ? (
              <button onClick={handleLogoutClick} className="btn btn-outline" style={{ padding: '0.45rem 1rem', fontSize: '0.85rem' }}>
                Logout <LogOut size={14} />
              </button>
            ) : (
              <Link to="/login" className="btn btn-primary" style={{ padding: '0.45rem 1.25rem', fontSize: '0.85rem' }}>
                Sign In <Calendar size={14} />
              </Link>
            )}
          </nav>

          {/* Mobile Menu Trigger */}
          <button 
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            style={{
              background: 'none',
              border: 'none',
              color: 'white',
              cursor: 'pointer',
              display: 'none' // Controlled in inline media-style CSS below
            }}
            className="mobile-trigger"
          >
            {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
          </button>
        </div>

        {/* Mobile Navigation Drawer */}
        {mobileMenuOpen && (
          <div style={{
            background: 'var(--bg-main)',
            borderBottom: '1px solid var(--glass-border)',
            padding: '1.5rem',
            display: 'flex',
            flexDirection: 'column',
            gap: '1.25rem'
          }} className="mobile-drawer">
            <Link to="/">Home</Link>
            <Link to="/doctors">Doctors</Link>
            <Link to="/contact">Contact</Link>
            
            {user?.role === 'patient' && (
              <>
                <Link to="/patient">Dashboard</Link>
                <Link to="/patient/book">Book Appointment</Link>
              </>
            )}
            
            {user?.role === 'doctor' && (
              <Link to="/doctor">Consultations</Link>
            )}
            
            {user?.role === 'admin' && (
              <>
                <Link to="/admin">Control Panel</Link>
                <Link to="/admin/appointments">Appointments</Link>
                <Link to="/admin/doctors">Roster</Link>
                <Link to="/admin/departments">Depts</Link>
              </>
            )}

            {user ? (
              <button onClick={handleLogoutClick} className="btn btn-danger" style={{ width: '100%' }}>
                Logout <LogOut size={16} />
              </button>
            ) : (
              <Link to="/login" className="btn btn-primary" style={{ width: '100%', textAlign: 'center' }}>
                Sign In <Calendar size={16} />
              </Link>
            )}
          </div>
        )}

        {/* Inline CSS styling hack to inject responsive nav stylesheet */}
        <style dangerouslySetInnerHTML={{__html: `
          .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
            transition: var(--transition-fast);
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
          }
          .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
          }
          .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            background-color: rgba(59, 130, 246, 0.1);
          }
          .mobile-drawer a {
            text-decoration: none;
            color: var(--text-main);
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
          }
          @media (max-width: 768px) {
            .desktop-nav {
              display: none !important;
            }
            .mobile-trigger {
              display: block !important;
            }
          }
        `}} />
      </header>

      {/* Main Page Layout Wrapper */}
      <main className="main-content">
        {children}
      </main>

      {/* Footer */}
      <footer style={{
        background: 'rgba(9, 13, 22, 0.95)',
        borderTop: '1px solid var(--glass-border)',
        padding: '2rem 1rem',
        marginTop: '4rem'
      }}>
        <div style={{
          maxWidth: '1200px',
          margin: '0 auto',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          flexWrap: 'wrap',
          gap: '1.5rem',
          fontSize: '0.85rem',
          color: 'var(--text-muted)'
        }}>
          <div>
            <p style={{ margin: 0 }}>© 2026 Antigravity Digital Hospital. Group Project. All rights reserved.</p>
          </div>
          <div style={{ display: 'flex', gap: '1.5rem' }}>
            <Link to="/" style={{ color: 'var(--text-muted)', textDecoration: 'none' }}>Home</Link>
            <Link to="/doctors" style={{ color: 'var(--text-muted)', textDecoration: 'none' }}>Roster</Link>
            <Link to="/contact" style={{ color: 'var(--text-muted)', textDecoration: 'none' }}>Support</Link>
          </div>
        </div>
      </footer>
    </div>
  );
}
