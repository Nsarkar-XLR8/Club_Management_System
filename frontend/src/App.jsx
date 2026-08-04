import React, { useState } from 'react';
import { AuthProvider } from './context/AuthContext';
import SmoothScroll from './components/SmoothScroll';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import AuthModal from './components/AuthModal';

import HomePage from './pages/HomePage';
import ClubDetailsPage from './pages/ClubDetailsPage';
import EventsPage from './pages/EventsPage';
import ForumPage from './pages/ForumPage';
import DonorPage from './pages/DonorPage';
import DashboardPage from './pages/DashboardPage';
import ElectionsPage from './pages/ElectionsPage';
import FacilityBookingPage from './pages/FacilityBookingPage';
import IDCardPage from './pages/IDCardPage';
import AlumniPage from './pages/AlumniPage';

export default function App() {
  const [activeTab, setActiveTab] = useState('home');
  const [authModalOpen, setAuthModalOpen] = useState(false);

  return (
    <AuthProvider>
      <SmoothScroll>
        <div className="min-h-screen bg-[#F8F9FA] text-slate-900 flex flex-col justify-between selection:bg-[#F26522] selection:text-white">
        
        {/* Navigation Bar */}
        <Navbar
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          onOpenAuth={() => setAuthModalOpen(true)}
        />

        {/* Main Content Area */}
        <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 flex-grow w-full">
          {activeTab === 'home' && <HomePage setActiveTab={setActiveTab} onOpenAuth={() => setAuthModalOpen(true)} />}
          
          {(activeTab === 'computer_club' || activeTab === 'robotics_club' || activeTab === 'social_service' || activeTab === 'forum_club') && (
            <ClubDetailsPage clubId={activeTab} onOpenAuth={() => setAuthModalOpen(true)} />
          )}

          {activeTab === 'events' && <EventsPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'elections' && <ElectionsPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'facilities' && <FacilityBookingPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'forum' && <ForumPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'donors' && <DonorPage />}
          {activeTab === 'alumni' && <AlumniPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'idcard' && <IDCardPage onOpenAuth={() => setAuthModalOpen(true)} />}
          {activeTab === 'dashboard' && <DashboardPage />}
        </main>

        {/* Footer */}
        <Footer setActiveTab={setActiveTab} />

        {/* Auth Modal */}
        <AuthModal
          isOpen={authModalOpen}
          onClose={() => setAuthModalOpen(false)}
        />

      </div>
      </SmoothScroll>
    </AuthProvider>
  );
}
