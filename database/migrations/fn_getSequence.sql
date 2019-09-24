CREATE FUNCTION `getSequence`(
	`kode_sequence` VARCHAR(32),
	`tahun` INT
)
RETURNS INT(11)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
 DECLARE NO INT;
 SET no = (SELECT sequence FROM sequence WHERE kode_sequence = `kode_sequence` AND tahun = `tahun`);
 IF no IS NULL THEN
  SET no = 1;
  INSERT INTO sequence(kode_sequence, tahun, sequence) VALUES(`kode_sequence`, `tahun`, no);
 ELSE
  SET no = no + 1;
  UPDATE sequence SET sequence.sequence = no WHERE kode_sequence = `kode_sequence` AND tahun = `tahun`;
 END IF;
 RETURN no;
END