-- //////////////////////////////////////////////////////////////
-- //
-- //    Copyright (C) Thomas Kock, Delmenhorst, 2008, 2009
-- //
-- // This program is free software; you can redistribute it and/or modify
-- // it under the terms of the GNU General Public License as published by
-- // the Free Software Foundation; either version 2 of the License, or
-- // (at your option) any later version.
-- //
-- // This program is distributed in the hope that it will be useful,
-- // but WITHOUT ANY WARRANTY; without even the implied warranty of
-- // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
-- // GNU General Public License for more details.
-- //
-- // You should have received a copy of the GNU General Public License along
-- // with this program; if not, write to the Free Software Foundation, Inc.,
-- // 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
-- // http://www.gnu.org/copyleft/gpl.html
-- //
-- //////////////////////////////////////////////////////////////
-- postgresql
-- Note: If your wiki is using DB prefix
--       please replace fchw_relation to  (prefix)fchw_relation
--       for example DB prefix = MYDB_         MYDB_fchw_relation
-- You can check current db prefix on wiki Special:Check flowchart wiki page

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
	      
    
