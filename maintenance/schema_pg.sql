
    CREATE TABLE fchw_relation (
	from_id		integer not null,
	from_title	text	not null,
	to_id		integer null,
	to_title	text	not null,
	relation 	text	not null 
    );
    
    CREATE INDEX fchw_relation_idx_tt ON fchw_relation(to_title);
    CREATE INDEX fchw_relation_idx_r ON fchw_relation(relation);
    CREATE INDEX fchw_relation_idx_fir ON fchw_relation(from_id, relation);
    CREATE INDEX fchw_relation_idx_ftr ON fchw_relation(from_title, relation);
	      
    
