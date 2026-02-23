-- IMPORTANT: Back up your database before running this script!
-- These commands update the database columns to support up to 4 decimal places.
-- If you have a custom table prefix, replace 'tbl' with your prefix.

ALTER TABLE tblitems MODIFY rate DECIMAL(15,4);
ALTER TABLE tblitemable MODIFY rate DECIMAL(15,4);

ALTER TABLE tblinvoices MODIFY subtotal DECIMAL(15,4);
ALTER TABLE tblinvoices MODIFY total DECIMAL(15,4);
ALTER TABLE tblinvoices MODIFY total_tax DECIMAL(15,4);
ALTER TABLE tblinvoices MODIFY discount_total DECIMAL(15,4);
ALTER TABLE tblinvoices MODIFY adjustment DECIMAL(15,4);

ALTER TABLE tblestimates MODIFY subtotal DECIMAL(15,4);
ALTER TABLE tblestimates MODIFY total DECIMAL(15,4);
ALTER TABLE tblestimates MODIFY total_tax DECIMAL(15,4);
ALTER TABLE tblestimates MODIFY discount_total DECIMAL(15,4);
ALTER TABLE tblestimates MODIFY adjustment DECIMAL(15,4);

ALTER TABLE tblproposals MODIFY subtotal DECIMAL(15,4);
ALTER TABLE tblproposals MODIFY total DECIMAL(15,4);
ALTER TABLE tblproposals MODIFY total_tax DECIMAL(15,4);
ALTER TABLE tblproposals MODIFY discount_total DECIMAL(15,4);
ALTER TABLE tblproposals MODIFY adjustment DECIMAL(15,4);

ALTER TABLE tblcreditnotes MODIFY subtotal DECIMAL(15,4);
ALTER TABLE tblcreditnotes MODIFY total DECIMAL(15,4);
ALTER TABLE tblcreditnotes MODIFY total_tax DECIMAL(15,4);
ALTER TABLE tblcreditnotes MODIFY discount_total DECIMAL(15,4);
ALTER TABLE tblcreditnotes MODIFY adjustment DECIMAL(15,4);

ALTER TABLE tblexpenses MODIFY amount DECIMAL(15,4);
