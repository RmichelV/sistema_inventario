import jsPDF from 'jspdf';

type ProductoNotaVenta = {
  nombre: string;
  cantidad: number;
};

interface NotaVentaPDFParams {
  empresa: string;
  cliente: string;
  codigo: string;
  fecha: string;
  productos: ProductoNotaVenta[];
  precioFinal: number;
}

export function generarNotaVentaPDF({ empresa, cliente,codigo, fecha, productos, precioFinal }: NotaVentaPDFParams) {
  const doc = new jsPDF();

  // Título
  doc.setFontSize(16);
  doc.text(empresa, 10, 15);

  // Datos del cliente y fecha
  doc.setFontSize(12);
  let y = 30;
  doc.text(`Cliente: ${cliente}`, 10, y);
  y += 8;
  doc.text(`Codigo: ${codigo}`, 10, y);
  y += 8;
  doc.text(`Fecha: ${fecha}`, 10, y);

  // Tabla de productos
  y += 12;
  doc.text('Productos:', 10, y);
  y += 8;
  doc.setFontSize(11);
  doc.text('Producto', 12, y);
  doc.text('Cantidad', 100, y);
  y += 6;
  productos.forEach((p: ProductoNotaVenta) => {
    doc.text(String(p.nombre), 12, y);
    doc.text(String(p.cantidad), 100, y);
    y += 6;
  });

  // Precio final
  y += 8;
  doc.setFontSize(13);
  doc.text(`Precio final de la venta: ${precioFinal}`, 10, y);

  doc.save(`nota-venta-${cliente}-${fecha}.pdf`);
}
