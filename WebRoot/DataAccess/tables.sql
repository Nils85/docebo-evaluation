create table node_tree (
	idNode int primary key,
	level int not null,
	iLeft int not null,
	iRight int not null
);

create table node_tree_names (
	idNode int not null,
	language varchar(255) not null,
	nodeName varchar(255) not null,
	constraint fk_node_tree
		foreign key (idNode)
		references node_tree(idNode)
		on update restrict
		on delete restrict
);