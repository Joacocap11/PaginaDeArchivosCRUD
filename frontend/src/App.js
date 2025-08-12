import React, { useState } from 'react';
import FirmwareList from './FirmwareList';
import FirmwareUpload from './FirmwareUpload';

function App() {
  const [refreshList, setRefreshList] = useState(false);

  const handleUpload = () => {
    setRefreshList((prev) => !prev);
  };

  return (
    <div className="container mt-4">
      <h1 className="mb-4">Gestor de Firmwares</h1>
      <FirmwareUpload onUpload={handleUpload} />
      <FirmwareList refresh={refreshList} />
    </div>
  );
}

export default App;
