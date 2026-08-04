import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { X, LogIn, UserPlus, Lock, Mail, User, Hash, GraduationCap } from 'lucide-react';

export default function AuthModal({ isOpen, onClose }) {
  const { login, register } = useAuth();
  const [isRegister, setIsRegister] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  // Form states
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [name, setName] = useState('');
  const [studentId, setStudentId] = useState('');
  const [department, setDepartment] = useState('CSE');

  if (!isOpen) return null;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    if (isRegister) {
      const res = await register(studentId, name, email, password, department);
      if (res.success) {
        onClose();
      } else {
        setError(res.message || 'Registration failed');
      }
    } else {
      const res = await login(email, password);
      if (res.success) {
        onClose();
      } else {
        setError(res.message || 'Invalid email or password');
      }
    }
    setLoading(false);
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
      
      {/* Background animated blobs for modal */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-[#F26522]/20 rounded-full blur-3xl pointer-events-none animate-blob"></div>
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-[#003366]/20 rounded-full blur-3xl pointer-events-none animate-blob" style={{ animationDelay: '2s' }}></div>

      <div className="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-2xl relative z-10 transform transition-all duration-300 scale-100 opacity-100">
        
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 transition-colors z-20"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Title */}
        <div className="text-center mb-6 relative">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#003366] to-[#004080] flex items-center justify-center font-black text-2xl text-white mx-auto mb-4 shadow-xl shadow-blue-900/20 transform hover:scale-105 transition-transform duration-300">
            UIU
          </div>
          <h3 className="text-2xl font-bold text-slate-900 font-serif">
            {isRegister ? 'Create Account' : 'Welcome Back'}
          </h3>
          <p className="text-xs font-medium text-slate-500 mt-1.5">
            {isRegister ? 'Register your UIU student profile' : 'Sign in to access your dashboard'}
          </p>
        </div>

        {error && (
          <div className="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs text-center font-bold shadow-sm animate-bounce">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          
          {isRegister && (
            <div className="animate-fadeIn space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1.5">Full Name</label>
                <div className="relative group">
                  <User className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 group-focus-within:text-[#F26522] transition-colors" />
                  <input
                    type="text"
                    required
                    placeholder="e.g. Tanvir Hossain"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522] focus:bg-white transition-all shadow-sm"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1.5">Student ID</label>
                  <div className="relative group">
                    <Hash className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 group-focus-within:text-[#F26522] transition-colors" />
                    <input
                      type="text"
                      placeholder="011221001"
                      value={studentId}
                      onChange={(e) => setStudentId(e.target.value)}
                      className="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522] focus:bg-white transition-all shadow-sm"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1.5">Department</label>
                  <div className="relative group">
                    <GraduationCap className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 group-focus-within:text-[#F26522] transition-colors pointer-events-none z-10" />
                    <select
                      value={department}
                      onChange={(e) => setDepartment(e.target.value)}
                      className="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522] focus:bg-white transition-all shadow-sm appearance-none relative"
                    >
                      <option value="CSE">CSE</option>
                      <option value="EEE">EEE</option>
                      <option value="Civil">Civil</option>
                      <option value="BBA">BBA</option>
                      <option value="English">English</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          )}

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
            <div className="relative group">
              <Mail className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 group-focus-within:text-[#F26522] transition-colors" />
              <input
                type="email"
                required
                placeholder="admin@uiu.ac.bd"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522] focus:bg-white transition-all shadow-sm"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
            <div className="relative group">
              <Lock className="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 group-focus-within:text-[#F26522] transition-colors" />
              <input
                type="password"
                required
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:outline-none focus:border-[#F26522] focus:ring-1 focus:ring-[#F26522] focus:bg-white transition-all shadow-sm"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full uiu-gradient-btn py-3.5 rounded-xl text-sm font-bold text-white uppercase tracking-wider flex items-center justify-center gap-2 mt-6 shadow-md hover:shadow-lg transition-all"
          >
            {loading ? (
              <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            ) : isRegister ? (
              <>
                <UserPlus className="w-5 h-5" /> Register Profile
              </>
            ) : (
              <>
                <LogIn className="w-5 h-5" /> Sign In securely
              </>
            )}
          </button>
        </form>

        <div className="mt-8 text-center text-xs font-medium text-slate-500">
          {isRegister ? (
            <p>
              Already have an account?{' '}
              <button
                onClick={() => { setIsRegister(false); setError(''); }}
                className="text-[#F26522] font-bold hover:text-[#003366] transition-colors ml-1 underline decoration-[#F26522]/30 underline-offset-2"
              >
                Sign In
              </button>
            </p>
          ) : (
            <p>
              Don't have an account yet?{' '}
              <button
                onClick={() => { setIsRegister(true); setError(''); }}
                className="text-[#F26522] font-bold hover:text-[#003366] transition-colors ml-1 underline decoration-[#F26522]/30 underline-offset-2"
              >
                Register Student Account
              </button>
            </p>
          )}
        </div>

        {/* Quick Demo Hint */}
        <div className="mt-6 pt-5 border-t border-slate-100 text-[10px] text-slate-400 text-center font-medium">
          💡 Demo Admin: <code className="text-[#003366] font-bold bg-blue-50 px-1.5 py-0.5 rounded shadow-sm">admin@uiu.ac.bd</code> / <code className="text-[#003366] font-bold bg-blue-50 px-1.5 py-0.5 rounded shadow-sm">password123</code>
        </div>

      </div>
    </div>
  );
}
