window.craftEssentials = (function() {
  function registerAjaxColumns(options) {
    const row = document.getElementById(options.id);
    if (!row) {
      return;
    }
    
    for (const id of options.columns) {
      const column = document.getElementById(id);
      while (
        column.nextElementSibling &&
        column.nextElementSibling !== row &&
        !column.nextElementSibling.classList.contains('ceGrid__column')
      ) {
        column.append(column.nextElementSibling);
      }

      row.append(column);
    }
  }

  return {
    registerAjaxColumns,
  };
})();
