import React, { useState } from 'react';
import axios from 'axios';

const FirmwareUpload = ({ onUpload }) => {
  const [file, setFile] = useState(null);
  const [version, setVersion] = useState('');
  const [description, setDescription] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!file) {
      setError('Seleccioná un archivo');
      return;
    }
    setError(null);
    setLoading(true);

    const formData = new FormData();
    formData.append('file', file);
    formData.append('version', version);
    formData.append('description', description);

    // debug: listar contenido
    for (let pair of formData.entries()) {
      console.log(pair[0], pair[1]);
    }

    try {
      const res = await axios.post('http://localhost:8000/api/firmwares', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      console.log('Respuesta upload:', res.data);
      setFile(null);
      setVersion('');
      setDescription('');
      if (onUpload) onUpload();
    } catch (err) {
      console.error('Error al subir firmware', err.response || err);
      setError('Error al subir firmware: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="mb-4">
      <div className="mb-2">
        <input type="file" onChange={handleFileChange} className="form-control" />
      </div>

      <div className="mb-2">
        <input
          type="text"
          placeholder="Versión (opcional)"
          value={version}
          onChange={(e) => setVersion(e.target.value)}
          className="form-control"
        />
      </div>

      <div className="mb-2">
        <textarea
          placeholder="Descripción (opcional)"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          className="form-control"
          rows="3"
        />
      </div>

      {error && <div className="text-danger mb-2">{error}</div>}

      <button type="submit" className="btn btn-success" disabled={loading}>
        {loading ? 'Subiendo...' : 'Subir Firmware'}
      </button>
    </form>
  );
};

export default FirmwareUpload;
