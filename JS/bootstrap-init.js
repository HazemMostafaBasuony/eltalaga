/**
 * Bootstrap Initialization Script
 * Este script inicializa los componentes de Bootstrap en todas las páginas
 */

document.addEventListener('DOMContentLoaded', function() {
  // Inicializar tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Inicializar popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Inicializar dropdowns
  var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
  dropdownElementList.map(function (dropdownToggleEl) {
    return new bootstrap.Dropdown(dropdownToggleEl);
  });

  // Inicializar modales
  document.querySelectorAll('.modal').forEach(function(modalEl) {
    modalEl.addEventListener('shown.bs.modal', function() {
      // Cuando se muestra un modal, enfocar el primer input
      const firstInput = this.querySelector('input, select, textarea');
      if (firstInput) {
        firstInput.focus();
      }
    });
  });

  // Inicializar alertas descartables
  document.querySelectorAll('.alert').forEach(function(alertEl) {
    if (alertEl.querySelector('.btn-close')) {
      const closeBtn = alertEl.querySelector('.btn-close');
      closeBtn.addEventListener('click', function() {
        const alert = bootstrap.Alert.getOrCreateInstance(alertEl);
        alert.close();
      });
    }
  });

  // Inicializar validación de formularios
  document.querySelectorAll('form.needs-validation').forEach(function(form) {
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
});