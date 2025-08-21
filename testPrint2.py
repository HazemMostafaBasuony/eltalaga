from flask import Flask, request, jsonify
from flask_cors import CORS
import threading
import mysql.connector
import win32print
import win32ui
import win32con
import arabic_reshaper
from bidi.algorithm import get_display
from datetime import datetime
import qrcode
import os
from PIL import Image, ImageWin
import tempfile

app = Flask(__name__)
CORS(app)

class InvoicePrintingSystem:
    def __init__(self):
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'talagadb',
            'charset': 'utf8',
            'time_zone': '+03:00'
        }
        self.current_invoice = None
        self.current_supplier = None
        self.current_branch = None
        self.current_items = []
        
        self.printer_settings = {
            'cash': 'cash',
            'a4':'PDF24' # Changed for testing, set to your desired A4 printer
        }
        
        self.logo_path = "assets/images/logo4.png"
    
    def set_printer(self, printer_type, printer_name):
        self.printer_settings[printer_type] = printer_name
        return True

    def process_invoice(self, invoice_id):
        try:
            self._load_invoice_data(invoice_id)
            self._print_invoice("cash") # Or "a4" depending on your need
            return True, f"تم طباعة الفاتورة رقم {invoice_id} بنجاح"
        except Exception as e:
            return False, f"خطأ في معالجة الفاتورة: {str(e)}"

    def _load_invoice_data(self, invoice_id):
        conn = None
        try:
            conn = mysql.connector.connect(**self.db_config)
            cursor = conn.cursor(dictionary=True)
            
            cursor.execute("SELECT * FROM invoices WHERE invoiceID = %s", (invoice_id,))
            self.current_invoice = cursor.fetchone()
            if not self.current_invoice:
                raise Exception("لم يتم العثور على الفاتورة")
            
            supplier_id = self.current_invoice['fromID']
            cursor.execute("SELECT * FROM suppliers WHERE supplierID = %s", (supplier_id,))
            self.current_supplier = cursor.fetchone()
            if not self.current_supplier:
                raise Exception("لم يتم العثور على بيانات المورد")
            
            cursor.execute("SELECT * FROM branchs LIMIT 1")
            self.current_branch = cursor.fetchone()
            if not self.current_branch:
                raise Exception("لم يتم العثور على بيانات الفرع")
            
            cursor.execute("""
                SELECT ia.*, i.itemName 
                FROM itemaction ia
                JOIN itemscard i ON ia.itemID = i.itemID
                WHERE ia.invoiceID = %s
            """, (invoice_id,))
            self.current_items = cursor.fetchall()
            
        except Exception as e:
            raise Exception(f"خطأ في قراءة بيانات الفاتورة: {str(e)}")
        finally:
            if conn and conn.is_connected():
                cursor.close()
                conn.close()

    def _print_invoice(self, printer_type):
        printer_name = self.printer_settings.get(printer_type, win32print.GetDefaultPrinter())
        
        try:
            hdc = win32ui.CreateDC()
            hdc.CreatePrinterDC(printer_name)
            hdc.StartDoc("Invoice")
            hdc.StartPage()
            
            printer_width = hdc.GetDeviceCaps(win32con.HORZRES)
            printer_height = hdc.GetDeviceCaps(win32con.VERTRES)

            # Define fonts
            title_font = win32ui.CreateFont({
                "name": "Arial",
                "height": 40,
                "weight": 600,
            })
            
            header_font = win32ui.CreateFont({
                "name": "Arial",
                "height": 30,
                "weight": 500,
            })
            
            item_font = win32ui.CreateFont({
                "name": "Arial",
                "height": 25,
                "weight": 400,
            })
            
            small_font = win32ui.CreateFont({
                "name": "Arial",
                "height": 20,
                "weight": 300,
            })
            
            # Define pens
            thick_pen = win32ui.CreatePen(win32con.PS_SOLID, 3, 0)
            normal_pen = win32ui.CreatePen(win32con.PS_SOLID, 1, 0)

            def print_centered(text, y, font=title_font):
                hdc.SelectObject(font)
                reshaped_text = arabic_reshaper.reshape(text)
                bidi_text = get_display(reshaped_text)
                text_width = hdc.GetTextExtent(bidi_text)[0]
                x = (printer_width - text_width) // 2
                hdc.TextOut(x, y, bidi_text)
                return y + hdc.GetTextExtent(bidi_text)[1] + 10
            
            def print_right(text, y, font=title_font):
                hdc.SelectObject(font)
                reshaped_text = arabic_reshaper.reshape(text)
                bidi_text = get_display(reshaped_text)
                text_width = hdc.GetTextExtent(bidi_text)[0]
                x = printer_width - text_width - 50
                hdc.TextOut(x, y, bidi_text)
                return y + hdc.GetTextExtent(bidi_text)[1] + 10
            
            def draw_thick_line(x1, y1, x2, y2):
                hdc.SelectObject(thick_pen)
                hdc.MoveTo(x1, y1)
                hdc.LineTo(x2, y2)
                hdc.SelectObject(normal_pen)

            y_pos = 50
            
            # Print Logo
            try:
                if os.path.exists(self.logo_path):
                    cm_to_points = 28.35
                    item_size = int(6 * cm_to_points)
                    logo_x = printer_width - 50 - item_size
                    
                    logo_img = Image.open(self.logo_path)
                    logo_img = logo_img.resize((item_size, item_size), Image.LANCZOS)
                    logo_bmp = ImageWin.Dib(logo_img)
                    logo_bmp.draw(hdc.GetHandleOutput(), (logo_x, y_pos, logo_x + item_size, y_pos + item_size))
                    
                    y_pos += item_size + 40
            except Exception as e:
                print(f"خطأ في طباعة الشعار: {e}")

            # Print Invoice Title
            y_pos = print_centered("أمر توريد بضاعة", y_pos, title_font)
            y_pos += 20
            
            # Print Invoice Info
            y_pos = print_centered(f"رقم الفاتورة: {self.current_invoice['invoiceID']}", y_pos, header_font)
            y_pos = print_centered(f"التاريخ: {self.current_invoice['date']}", y_pos, item_font)
            y_pos += 20
            
            # Separator line
            draw_thick_line(50, y_pos, printer_width - 50, y_pos)
            y_pos += 30
            
            # Print Branch Info
            y_pos = print_centered("بيانات الفرع", y_pos, header_font)
            y_pos = print_centered(f"اسم الفرع: {self.current_branch['branchName']}", y_pos, item_font)
            y_pos = print_centered(f"السجل التجاري: {self.current_branch['numberRC']}", y_pos, item_font)
            y_pos = print_centered(f"الرقم الضريبي: {self.current_branch['numberTax']}", y_pos, item_font)
            y_pos = print_centered(f"العنوان: {self.current_branch['street']} - {self.current_branch['city']}", y_pos, item_font)
            y_pos += 20
            
            # Separator line
            draw_thick_line(50, y_pos, printer_width - 50, y_pos)
            y_pos += 30
            
            # Print Supplier Info
            y_pos = print_centered("بيانات المورد", y_pos, header_font)
            y_pos = print_centered(f"اسم المورد: {self.current_supplier['supplierName']}", y_pos, item_font)
            y_pos = print_centered(f"السجل التجاري: {self.current_supplier['numberRC']}", y_pos, item_font)
            y_pos = print_centered(f"الرقم الضريبي: {self.current_supplier['numberTax']}", y_pos, item_font)
            y_pos = print_centered(f"الهاتف: {self.current_supplier['phone']}", y_pos, item_font)
            y_pos = print_centered(f"العنوان: {self.current_supplier['street']} - {self.current_supplier['city']}", y_pos, item_font)
            y_pos += 20
            
            # Separator line
            draw_thick_line(50, y_pos, printer_width - 50, y_pos)
            y_pos += 30
            
            # Print Items Table
            y_pos = print_centered("المنتجات الموردة", y_pos, header_font)
            y_pos += 20
            
            # Column headers
            col_widths = [150, 300, 150, 150]
            total_width = sum(col_widths)
            start_x = (printer_width - total_width) // 2
            
            draw_thick_line(start_x, y_pos, start_x + total_width, y_pos)
            y_pos += 10
            
            hdc.SelectObject(header_font)
            headers = ["الكمية", "الصنف", "السعر", "الإجمالي"]
            
            for i, header in enumerate(headers):
                x = start_x + sum(col_widths[:i]) + (col_widths[i] - hdc.GetTextExtent(header)[0]) // 2
                hdc.TextOut(x, y_pos, header)
            
            y_pos += hdc.GetTextExtent(headers[0])[1] + 10
            
            draw_thick_line(start_x, y_pos, start_x + total_width, y_pos)
            y_pos += 15
            
            # Print Items
            hdc.SelectObject(item_font)
            total_amount = 0
            
            for item in self.current_items:
                quantity = str(item['count'])
                x = start_x + (col_widths[0] - hdc.GetTextExtent(quantity)[0]) // 2
                hdc.TextOut(x, y_pos, quantity)
                
                item_name = item['itemName']
                x = start_x + col_widths[0] + (col_widths[1] - hdc.GetTextExtent(item_name)[0]) // 2
                hdc.TextOut(x, y_pos, item_name)
                
                price = f"{item['price']:.2f}"
                x = start_x + col_widths[0] + col_widths[1] + (col_widths[2] - hdc.GetTextExtent(price)[0]) // 2
                hdc.TextOut(x, y_pos, price)
                
                item_total = item['count'] * item['price']
                total_amount += item_total
                item_total_str = f"{item_total:.2f}"
                x = start_x + col_widths[0] + col_widths[1] + col_widths[2] + (col_widths[3] - hdc.GetTextExtent(item_total_str)[0]) // 2
                hdc.TextOut(x, y_pos, item_total_str)
                
                y_pos += hdc.GetTextExtent(item_name)[1] + 15
            
            draw_thick_line(start_x, y_pos, start_x + total_width, y_pos)
            y_pos += 30
            
            # Print Totals
            hdc.SelectObject(header_font)
            
            if 'discount' in self.current_invoice and self.current_invoice['discount'] > 0:
                discount = self.current_invoice['discount']
                y_pos = print_right(f"الخصم: {discount:.2f}", y_pos)
                total_amount -= discount
            
            if 'vat' in self.current_invoice and self.current_invoice['vat'] > 0:
                vat = self.current_invoice['vat']
                y_pos = print_right(f"الضريبة: {vat:.2f}", y_pos)
                total_amount += vat
            
            y_pos = print_right(f"الإجمالي النهائي: {total_amount:.2f}", y_pos)
            y_pos += 30
            
            # Print Invoice Notes
            if 'note' in self.current_invoice and self.current_invoice['note']:
                y_pos = print_centered("ملاحظات:", y_pos, header_font)
                y_pos = print_centered(self.current_invoice['note'], y_pos, item_font)
                y_pos += 30

            # --- QR Code Printing ---
            # Assume originalInvoicePath is available from self.current_invoice
            # You need to ensure 'originalInvoicePath' exists in your invoices table and is fetched.
            # For demonstration, I'll use a placeholder.
            original_invoice_path_data = self.current_invoice.get('originalInvoicePath', f"localhost/uploads/invoices/{self.current_invoice['invoiceID']}")

            if original_invoice_path_data:
                qr = qrcode.QRCode(
                    version=1,
                    error_correction=qrcode.constants.ERROR_CORRECT_H, # Higher error correction for better readability
                    box_size=4, # Smaller box size for compact QR code
                    border=4,
                )
                qr.add_data(original_invoice_path_data)
                qr.make(fit=True)

                qr_img = qr.make_image(fill_color="black", back_color="white").convert('RGB')
                
                # Resize QR code for printing (adjust as needed)
                qr_code_size = int(5 * cm_to_points) # 5 cm for example
                qr_img = qr_img.resize((qr_code_size, qr_code_size), Image.LANCZOS)

                # Calculate position for QR code (e.g., center or near the bottom)
                qr_x = (printer_width - qr_code_size) // 2
                qr_y = y_pos + 20 # A bit below the last printed text

                # Draw QR code
                qr_bmp = ImageWin.Dib(qr_img)
                qr_bmp.draw(hdc.GetHandleOutput(), (qr_x, qr_y, qr_x + qr_code_size, qr_y + qr_code_size))
                
                y_pos = qr_y + qr_code_size + 30 # Update y_pos after QR code

            # --- End QR Code Printing ---
            
            # Print Signatures
            y_pos = print_centered("توقيع المورد: ___________________", y_pos, header_font)
            y_pos += 20
            y_pos = print_centered("ختم المورد", y_pos, header_font)
            y_pos += 30
            
            y_pos = print_centered("توقيع المسؤول: ___________________", y_pos, header_font)
            y_pos += 20
            y_pos = print_centered("ختم المؤسسة", y_pos, header_font)
            
            hdc.EndPage()
            hdc.EndDoc()
            hdc.DeleteDC()
            
        except Exception as e:
            raise Exception(f"خطأ في الطباعة على طابعة {printer_name}: {str(e)}")

invoice_system = InvoicePrintingSystem()

@app.route('/set-printers', methods=['POST', 'OPTIONS'])
def set_printers():
    if request.method == 'OPTIONS':
        response = jsonify()
        response.headers.add('Access-Control-Allow-Origin', '*')
        response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
        response.headers.add('Access-Control-Allow-Methods', 'POST, OPTIONS')
        return response
    
    try:
        data = request.get_json()
        available_printers = [printer[2] for printer in win32print.EnumPrinters(2)]
        
        for printer_type in ['cash', 'a4']:
            if printer_type in data:
                if data[printer_type] in available_printers:
                    invoice_system.set_printer(printer_type, data[printer_type])
                else:
                    return jsonify({
                        "error": f"الطابعة {data[printer_type]} غير متاحة",
                        "available_printers": available_printers
                    }), 400
        
        return jsonify({
            "message": "تم تحديث إعدادات الطابعات بنجاح",
            "current_settings": invoice_system.printer_settings
        }), 200
        
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/print-number', methods=['POST', 'OPTIONS'])
def handle_print_request():
    if request.method == 'OPTIONS':
        response = jsonify()
        response.headers.add('Access-Control-Allow-Origin', '*')
        response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
        response.headers.add('Access-Control-Allow-Methods', 'POST, OPTIONS')
        return response
    
    try:
        data = request.get_json()
        invoice_id = data.get('number')
        
        if invoice_id is None:
            return jsonify({"error": "يجب إرسال رقم الفاتورة"}), 400
        
        try:
            invoice_id = int(invoice_id)
        except ValueError:
            return jsonify({"error": "رقم الفاتورة يجب أن يكون رقماً صحيحاً"}), 400

        def process_invoice_thread():
            success, message = invoice_system.process_invoice(invoice_id)
            print(f"النتيجة: {success} - {message}")
            
        threading.Thread(target=process_invoice_thread, daemon=True).start()
        
        return jsonify({
            "status": "جاري المعالجة",
            "message": "تم استلام أمر الطباعة",
            "invoice_number": invoice_id
        }), 200
            
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": f"خطأ غير متوقع: {str(e)}"
        }), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8080, debug=True)