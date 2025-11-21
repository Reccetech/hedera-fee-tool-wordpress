import { useState } from 'react'
import './App.css'
import FeeEstimator from './components/FeeEstimator';


function App() {
  return (
    <div className="min-h-screen p-6">
      <h1 className="text-2xl font-bold text-center mb-8">Hedera Fee Calculator v2</h1>
      <FeeEstimator />
    </div>
  );
}

export default App

