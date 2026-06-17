import React from 'react';
import { Link } from 'react-router-dom';
import { 
  Calendar, 
  Users, 
  Activity, 
  Clock, 
  Heart, 
  ShieldCheck, 
  ChevronRight,
  Sparkles
} from 'lucide-react';

export default function Home() {
  const services = [
    { name: 'Cardiology', desc: 'Heart care, ECG, and blood pressure therapies.', icon: Activity, color: '#ef4444' },
    { name: 'Pediatrics', desc: 'Specialized healthcare for infants, toddlers, and teens.', icon: Users, color: '#10b981' },
    { name: 'Neurology', desc: 'Expert diagnosis for brain, spinal, and nerve disorders.', icon: BrainIcon, color: '#3b82f6' },
    { name: 'Dermatology', desc: 'Treatment for skin, nails, hair, and cosmetic therapies.', icon: Sparkles, color: '#a855f7' },
    { name: 'General Medicine', desc: 'Comprehensive prevention, checkups, and cures.', icon: Heart, color: '#06b6d4' }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '4rem' }}>
      {/* Hero Section */}
      <section className="glass-card" style={{
        display: 'grid',
        gridTemplateColumns: '1.2fr 0.8fr',
        gap: '2rem',
        alignItems: 'center',
        padding: '3.5rem 3rem',
        position: 'relative',
        overflow: 'hidden'
      }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem', zIndex: 2 }}>
          <div style={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: '0.5rem',
            background: 'rgba(59, 130, 246, 0.1)',
            color: '#60a5fa',
            padding: '0.35rem 1rem',
            borderRadius: '50px',
            fontSize: '0.85rem',
            fontWeight: 600,
            width: 'fit-content'
          }}>
            <Activity size={14} /> 24/7 Digital Health Hub
          </div>
          <h1 style={{ fontSize: '3.2rem', marginBottom: '0.5rem' }}>
            Your Health Is Our <span style={{ color: 'var(--primary)', background: 'linear-gradient(to right, #3b82f6, #60a5fa)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}>First Priority</span>
          </h1>
          <p style={{ fontSize: '1.1rem', maxWidth: '600px' }}>
            Book appointments instantly, check doctor availabilities, and manage your health dashboard. Experience seamless hospital care powered by advanced digital schedules.
          </p>
          <div style={{ display: 'flex', gap: '1rem', marginTop: '1rem', flexWrap: 'wrap' }}>
            <Link to="/login" className="btn btn-primary">
              Book Appointment <Calendar size={18} />
            </Link>
            <Link to="/doctors" className="btn btn-outline">
              Meet Doctors <ChevronRight size={18} />
            </Link>
          </div>
        </div>

        {/* Decorative Grid Illustration */}
        <div style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          position: 'relative'
        }}>
          <div style={{
            width: '250px',
            height: '250px',
            borderRadius: '50%',
            background: 'radial-gradient(circle, var(--primary) 0%, transparent 70%)',
            filter: 'blur(30px)',
            position: 'absolute',
            opacity: 0.35,
            zIndex: 1
          }}></div>
          
          <div className="glass-card" style={{
            padding: '2rem',
            zIndex: 2,
            display: 'flex',
            flexDirection: 'column',
            gap: '1.25rem',
            background: 'rgba(15, 23, 42, 0.9)',
            border: '1px solid rgba(255, 255, 255, 0.12)',
            transform: 'rotate(2deg)'
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                background: 'var(--secondary)',
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}>
                <ShieldCheck size={20} color="white" />
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: '1.1rem' }}>Certified Doctors</h4>
                <p style={{ fontSize: '0.8rem', margin: 0 }}>100% Verified Staff</p>
              </div>
            </div>
            
            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                background: 'var(--primary)',
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}>
                <Clock size={20} color="white" />
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: '1.1rem' }}>Instant Booking</h4>
                <p style={{ fontSize: '0.8rem', margin: 0 }}>Skip Queue Lines</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Counter Section */}
      <section className="grid-4" style={{ gap: '1.5rem' }}>
        {[
          { number: '45+', title: 'Experienced Specialists', icon: Users, color: '#3b82f6' },
          { number: '15+', title: 'Medical Departments', icon: Activity, color: '#10b981' },
          { number: '12k+', title: 'Happy Patients Served', icon: Heart, color: '#ec4899' },
          { number: '24/7', title: 'Emergency Care', icon: Clock, color: '#f59e0b' }
        ].map((stat, idx) => (
          <div key={idx} className="glass-card" style={{
            display: 'flex',
            alignItems: 'center',
            gap: '1.25rem',
            padding: '1.5rem',
            background: 'linear-gradient(135deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.05) 100%)'
          }}>
            <div style={{
              background: `rgba(${hexToRgb(stat.color)}, 0.15)`,
              color: stat.color,
              width: '50px',
              height: '50px',
              borderRadius: '12px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}>
              <stat.icon size={24} />
            </div>
            <div>
              <h2 style={{ fontSize: '1.8rem', margin: 0, fontWeight: '800' }}>{stat.number}</h2>
              <p style={{ fontSize: '0.85rem', margin: 0, color: 'var(--text-muted)' }}>{stat.title}</p>
            </div>
          </div>
        ))}
      </section>

      {/* Hospital Services Grid */}
      <section style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
        <div style={{ textAlign: 'center', maxWidth: '600px', margin: '0 auto' }}>
          <h2 style={{ fontSize: '2.2rem' }}>Specialized Healthcare Departments</h2>
          <p>We provide full-spectrum medical assistance across various clinical departments with advanced tech and expert physicians.</p>
        </div>
        
        <div className="grid-3">
          {services.map((srv, idx) => (
            <div key={idx} className="glass-card" style={{
              display: 'flex',
              flexDirection: 'column',
              gap: '1rem',
              transition: 'var(--transition-fast)',
              cursor: 'default',
              position: 'relative',
              overflow: 'hidden'
            }}>
              <div style={{
                background: `rgba(${hexToRgb(srv.color)}, 0.1)`,
                color: srv.color,
                width: '45px',
                height: '45px',
                borderRadius: '10px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                marginBottom: '0.5rem'
              }}>
                <srv.icon size={22} />
              </div>
              <h3 style={{ fontSize: '1.25rem', margin: 0 }}>{srv.name}</h3>
              <p style={{ fontSize: '0.9rem', margin: 0 }}>{srv.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Trust Testimonial Cards */}
      <section className="glass-card" style={{
        display: 'grid',
        gridTemplateColumns: '1fr 1.5fr',
        gap: '2.5rem',
        padding: '3rem',
        alignItems: 'center'
      }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <h2 style={{ fontSize: '2rem', margin: 0 }}>What Patients Say About Us</h2>
          <p>Read inspiring experiences shared by our community patients who trusted us with their treatment and recoveries.</p>
        </div>
        <div className="glass-card" style={{
          background: 'rgba(255, 255, 255, 0.01)',
          border: '1px solid rgba(255, 255, 255, 0.05)',
          padding: '1.75rem',
          display: 'flex',
          flexDirection: 'column',
          gap: '1rem'
        }}>
          <p style={{ fontStyle: 'italic', fontSize: '1.05rem', color: '#e2e8f0' }}>
            "The booking system is super simple! I booked a cardiology checkup with Dr. Sarah Jenkins last night and received quick consultation today morning. Absolute lifesaver."
          </p>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <div style={{
              width: '40px',
              height: '40px',
              borderRadius: '50%',
              background: 'linear-gradient(135deg, #3b82f6 0%, #10b981 100%)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: '700',
              fontSize: '0.95rem'
            }}>JD</div>
            <div>
              <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Jane Doe</h4>
              <p style={{ margin: 0, fontSize: '0.75rem' }}>Cardiology Patient</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

// Brain icon placeholder
function BrainIcon(props) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width={props.size || 24} height={props.size || 24} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...props}>
      <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1 0-3.12 3 3 0 0 1 0-4.88 2.5 2.5 0 0 1 0-3.12A2.5 2.5 0 0 1 9.5 2Z" />
      <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 0-3.12 3 3 0 0 0 0-4.88 2.5 2.5 0 0 0 0-3.12A2.5 2.5 0 0 0 14.5 2Z" />
    </svg>
  );
}

// Hex code to RGB helper
function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : '255, 255, 255';
}
