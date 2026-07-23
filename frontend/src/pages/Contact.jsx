import React, { useState, useContext } from 'react';
import { Mail, Phone, MapPin, Send, HelpCircle, ChevronDown, ChevronUp, AlertCircle, CheckCircle } from 'lucide-react';
import { AuthContext } from '../App';

export default function Contact() {
  const { apiFetch } = useContext(AuthContext);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [msg, setMsg] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // Accordion state
  const [activeFaq, setActiveFaq] = useState(null);

  const faqs = [
    { q: 'How do I book an appointment?', a: 'You need to register as a patient or login. Once logged in, visit the Patient Dashboard and select the "Book Appointment" button. Select your department, preferred doctor, and pick an available date and time.' },
    { q: 'Can I cancel an appointment?', a: 'Yes. Patients can cancel appointments directly from their Patient Dashboard up to 2 hours before the scheduled time slot.' },
    { q: 'How do doctors manage their availability?', a: 'Doctors have a dedicated dashboard where they can see their patient schedule and directly write/edit their availability terms (e.g. specific days or hours).' },
    { q: 'What should I do in case of an emergency?', a: 'For acute emergencies, please call our 24/7 hotline at 1-800-555-9999 or proceed immediately to the Emergency Ward located at the ground floor.' }
  ];

  const handleContactSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!name || !email || !msg) {
      setError('Please fill in all required fields.');
      return;
    }

    setLoading(true);
    try {
      const response = await apiFetch('/contact.php', {
        method: 'POST',
        body: JSON.stringify({ name, email, message: msg })
      });

      if (response.success) {
        setSuccess('Your message has been sent successfully! We will get back to you shortly.');
        setName('');
        setEmail('');
        setMsg('');
        setTimeout(() => setSuccess(''), 5000);
      }
    } catch (err) {
      setError(err.message || 'Failed to send message. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const toggleFaq = (idx) => {
    if (activeFaq === idx) {
      setActiveFaq(null);
    } else {
      setActiveFaq(idx);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '3rem' }}>
      
      {/* Contact Header */}
      <section style={{ textAlign: 'center', maxWidth: '700px', margin: '0 auto' }}>
        <h1 style={{ fontSize: '2.5rem' }}>Get in Touch & About Us</h1>
        <p>Antigravity Digital Hospital bridges advanced healthcare with digital scheduling. Learn more about our systems or submit inquiries below.</p>
      </section>

      {/* Main Grid: Info & Form */}
      <section className="grid-2">
        {/* Info Column */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <div className="glass-card" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
            <h2 style={{ fontSize: '1.5rem', margin: 0 }}>Contact Details</h2>
            <p>Our administration is available Monday to Friday from 8:00 AM to 6:00 PM for general inquiries and department support.</p>
            
            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                background: 'rgba(59, 130, 246, 0.1)',
                color: 'var(--primary)',
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}>
                <MapPin size={20} />
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Our Location</h4>
                <p style={{ margin: 0, fontSize: '0.85rem' }}>777 Healthcare Blvd, Medical District, NY</p>
              </div>
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                background: 'rgba(16, 185, 129, 0.1)',
                color: 'var(--secondary)',
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}>
                <Phone size={20} />
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Call Us</h4>
                <p style={{ margin: 0, fontSize: '0.85rem' }}>1-800-ANTIGRAVITY (268-4472)</p>
              </div>
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                background: 'rgba(6, 182, 212, 0.1)',
                color: 'var(--info)',
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
              }}>
                <Mail size={20} />
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: '0.95rem' }}>General Email</h4>
                <p style={{ margin: 0, fontSize: '0.85rem' }}>care@antigravityhospital.com</p>
              </div>
            </div>
          </div>
        </div>

        {/* Contact Form */}
        <div className="glass-card">
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>Send Us a Message</h2>
          {error && (
            <div className="alert alert-error" style={{ marginBottom: '1.5rem' }}>
              <AlertCircle size={18} />
              <span>{error}</span>
            </div>
          )}
          {success && (
            <div className="alert alert-success" style={{ marginBottom: '1.5rem' }}>
              <CheckCircle size={18} />
              <span>{success}</span>
            </div>
          )}
          <form onSubmit={handleContactSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
            <div className="form-group">
              <label className="form-label">Name</label>
              <input 
                type="text" 
                className="form-input" 
                placeholder="Your Name" 
                value={name}
                onChange={(e) => setName(e.target.value)}
                disabled={loading}
                required
              />
            </div>
            
            <div className="form-group">
              <label className="form-label">Email</label>
              <input 
                type="email" 
                className="form-input" 
                placeholder="you@example.com" 
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                disabled={loading}
                required
              />
            </div>

            <div className="form-group">
              <label className="form-label">Message</label>
              <textarea 
                className="form-input" 
                rows="4" 
                placeholder="Type your message here..."
                style={{ resize: 'vertical' }}
                value={msg}
                onChange={(e) => setMsg(e.target.value)}
                disabled={loading}
                required
              ></textarea>
            </div>

            <button type="submit" className="btn btn-primary" style={{ width: 'fit-content' }} disabled={loading}>
              {loading ? 'Sending...' : 'Send Inquiries'} <Send size={16} />
            </button>
          </form>
        </div>
      </section>

      {/* FAQ Accordion Section */}
      <section style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
        <h2 style={{ fontSize: '1.8rem', textAlign: 'center' }}>Frequently Asked Questions</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem', maxWidth: '800px', margin: '0 auto', width: '100%' }}>
          {faqs.map((faq, idx) => (
            <div key={idx} className="glass-card" style={{
              padding: '1.25rem 1.5rem',
              cursor: 'pointer',
              border: activeFaq === idx ? '1px solid var(--primary)' : '1px solid var(--glass-border)'
            }} onClick={() => toggleFaq(idx)}>
              <div style={{
                display: 'flex',
                justifyContent: 'between',
                alignItems: 'center',
                justifyContent: 'space-between',
                fontWeight: 600,
                color: activeFaq === idx ? 'var(--primary)' : 'var(--text-main)'
              }}>
                <span style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                  <HelpCircle size={18} color="var(--primary)" />
                  {faq.q}
                </span>
                {activeFaq === idx ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
              </div>
              {activeFaq === idx && (
                <div style={{
                  marginTop: '1rem',
                  paddingTop: '1rem',
                  borderTop: '1px solid var(--glass-border)',
                  color: 'var(--text-muted)',
                  fontSize: '0.95rem',
                  lineHeight: '1.6'
                }}>
                  {faq.a}
                </div>
              )}
            </div>
          ))}
        </div>
      </section>

    </div>
  );
}
