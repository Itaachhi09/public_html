-- Seed approved incentive (procedure) types for ER duty
INSERT INTO incentive_types (code, name, description, is_approved) VALUES
('ER_CONSULT', 'ER Consult', 'Emergency room consultation', 1),
('SUTURING', 'Suturing', 'Wound suturing procedure', 1),
('DOA', 'DOA', 'Dead on arrival documentation', 1),
('WOUND_DEBRIDEMENT', 'Wound Debridement', 'Wound debridement procedure', 1),
('NGT', 'NGT', 'Nasogastric tube insertion', 1),
('FBR', 'Foreign Body Removal', 'Foreign body removal procedure', 1),
('FOLEY', 'Foley Catheter', 'Foley catheter insertion', 1),
('ABG', 'ABG', 'Arterial blood gas collection', 1),
('AMBUCON', 'Ambucon', 'Ambulance convoy support', 1),
('INTUBATION', 'Intubation', 'Endotracheal intubation', 1),
('I_AND_D', 'I and D', 'Incision and drainage', 1),
('OR_ASSIST', 'OR Assist', 'Operating room assistance', 1),
('MED_CERT', 'Medical Certificate', 'Medical certificate issuance', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_approved = VALUES(is_approved);
