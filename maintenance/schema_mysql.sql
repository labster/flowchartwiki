-- mysql
-- Note: If your wiki is using DB prefix
--       please replace fchw_relation to  (prefix)fchw_relation
--       for example DB prefix = MYDB         MYDBfchw_relation
-- You can check current db prefix on wiki Special:Check flowchart wiki page

CREATE TABLE fchw_relation (
  from_id         INT(8) UNSIGNED NOT NULL,
  from_title      VARCHAR(255) binary NOT NULL,
  to_id           INT(8) UNSIGNED NOT NULL,
  to_title        VARCHAR(255) binary NOT NULL,
  relation        VARCHAR(255) binary NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX fchw_relation_idx_tt ON fchw_relation(to_title);
CREATE INDEX fchw_relation_idx_r ON fchw_relation(relation);
CREATE INDEX fchw_relation_idx_fir ON fchw_relation(from_id, relation);
CREATE INDEX fchw_relation_idx_ftr ON fchw_relation(from_title, relation);
