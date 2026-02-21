-- Add archived column to employee_movements table for soft delete
ALTER TABLE employee_movements ADD COLUMN archived TINYINT(1) DEFAULT 0 AFTER updated_at;
CREATE INDEX idx_archived ON employee_movements(archived);
