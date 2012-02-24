-- mysql
CREATE TABLE fchw_relation (
  from_id         INT(8) UNSIGNED NOT NULL,
  from_title      VARCHAR(255) binary NOT NULL,
  to_id           INT(8) UNSIGNED NOT NULL,
  to_title        VARCHAR(255) binary NOT NULL,
  relation        VARCHAR(255) binary NOT NULL
);
CREATE INDEX fchw_relation_idx_tt ON fchw_relation(to_title);
CREATE INDEX fchw_relation_idx_r ON fchw_relation(relation);
CREATE INDEX fchw_relation_idx_fir ON fchw_relation(from_id, relation);
CREATE INDEX fchw_relation_idx_ftr ON fchw_relation(from_title, relation);
