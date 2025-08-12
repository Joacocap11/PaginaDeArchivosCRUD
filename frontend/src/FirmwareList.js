import React, { useState, useEffect } from 'react';
import axios from 'axios';

const FirmwareList = ({ refresh }) => {
  const [firmwares, setFirmwares] = useState([]);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [loading, setLoading] = useState(false);

  const fetchFirmwares = async (pageNum = 1, searchTerm = '') => {
    setLoading(true);
    try {
      const res = await axios.get('http://localhost:8000/api/firmwares', {
        params: {
          page: pageNum,
          search: searchTerm,
        },
      });
      setFirmwares(res.data.data || []);
      setPage(res.data.current_page || 1);
      setLastPage(res.data.last_page || 1);
    } catch (error) {
      console.error('Error fetching firmwares', error);
    } finally {
      setLoading(false);
    }
  };

  // Carga inicial
  useEffect(() => {
    fetchFirmwares();
  }, []);

  // Recarga cuando cambia la prop refresh (cuando subís)
  useEffect(() => {
    fetchFirmwares(1, search);
  }, [refresh]);

  const handleSearch = (e) => {
    const newSearch = e.target.value;
    setSearch(newSearch);
    fetchFirmwares(1, newSearch);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('¿Seguro querés eliminar este firmware?')) return;

    try {
      await axios.delete(`http://localhost:8000/api/firmwares/${id}`);
      fetchFirmwares(page, search);
    } catch (error) {
      console.error('Error deleting firmware', error);
      alert('Error al eliminar firmware');
    }
  };

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= lastPage) {
      fetchFirmwares(newPage, search);
    }
  };

  return (
    <div className="mt-4">
      <div className="mb-3">
        <input
          type="text"
          placeholder="Buscar firmware..."
          value={search}
          onChange={handleSearch}
          className="form-control"
        />
      </div>

      {loading ? (
        <div>Cargando...</div>
      ) : firmwares.length === 0 ? (
        <div className="alert alert-info">No se encontraron firmwares.</div>
      ) : (
        <ul className="list-group mb-3">
          {firmwares.map((fw) => (
            <li
              key={fw.id}
              className="list-group-item d-flex justify-content-between align-items-center"
            >
              <div>
                <strong>{fw.filename}</strong> - Versión: {fw.version || 'N/A'}
                <div className="small text-muted">
                  {fw.filesize ? `${fw.filesize} bytes` : ''}
                </div>
                {fw.url && (
                  <div>
                    <a
                      href={fw.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn btn-link btn-sm p-0"
                    >
                      Ver/Descargar
                    </a>
                  </div>
                )}
              </div>

              <div>
                <button
                  className="btn btn-danger btn-sm"
                  onClick={() => handleDelete(fw.id)}
                >
                  Eliminar
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}

      <div className="d-flex justify-content-between align-items-center">
        <button
          className="btn btn-primary"
          onClick={() => handlePageChange(page - 1)}
          disabled={page === 1}
        >
          Anterior
        </button>

        <span>
          Página {page} de {lastPage}
        </span>

        <button
          className="btn btn-primary"
          onClick={() => handlePageChange(page + 1)}
          disabled={page === lastPage}
        >
          Siguiente
        </button>
      </div>
    </div>
  );
};

export default FirmwareList;
