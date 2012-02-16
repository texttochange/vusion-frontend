COPY acos (id, parent_id, model, foreign_key, alias, lft, rght) FROM stdin;
31	28	\N	\N	add	59	60
3	2	\N	\N	index	3	4
4	2	\N	\N	view	5	6
32	28	\N	\N	edit	61	62
5	2	\N	\N	add	7	8
6	2	\N	\N	edit	9	10
2	1	\N	\N	Groups	2	13
7	2	\N	\N	delete	11	12
33	28	\N	\N	delete	63	64
8	1	\N	\N	Home	14	17
9	8	\N	\N	index	15	16
34	28	\N	\N	login	65	66
43	42	\N	\N	index	89	90
11	10	\N	\N	index	19	20
35	28	\N	\N	logout	67	68
12	10	\N	\N	view	21	22
13	10	\N	\N	add	23	24
45	42	\N	\N	add	91	92
14	10	\N	\N	edit	25	26
10	1	\N	\N	ProgramDocuments	18	29
15	10	\N	\N	delete	27	28
17	16	\N	\N	index	31	32
18	16	\N	\N	view	33	34
38	1	\N	\N	Participants	76	87
19	16	\N	\N	add	35	36
52	38	\N	\N	view	85	86
20	16	\N	\N	edit	37	38
16	1	\N	\N	Programs	30	41
21	16	\N	\N	delete	39	40
23	22	\N	\N	index	43	44
24	22	\N	\N	view	45	46
25	22	\N	\N	add	47	48
53	42	\N	\N	draft	93	94
26	22	\N	\N	edit	49	50
22	1	\N	\N	ProgramsUsers	42	53
27	22	\N	\N	delete	51	52
29	28	\N	\N	index	55	56
30	28	\N	\N	view	57	58
54	42	\N	\N	active	95	96
28	1	\N	\N	Users	54	71
36	1	\N	\N	AclExtras	72	73
37	1	\N	\N	Mongodb	74	75
39	38	\N	\N	index	77	78
42	1	\N	\N	Scripts	88	99
44	28	\N	\N	initDB	69	70
47	46	\N	\N	index	101	102
55	42	\N	\N	activate_draft	97	98
1	\N	\N	\N	controllers	1	104
46	1	\N	\N	Status	100	103
49	38	\N	\N	add	79	80
50	38	\N	\N	edit	81	82
51	38	\N	\N	delete	83	84
\.


--
-- Data for Name: aros; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY aros (id, parent_id, model, foreign_key, alias, lft, rght) FROM stdin;
1	\N	Group	1	\N	1	4
8	1	User	8	\N	2	3
5	\N	Group	2	\N	5	8
9	5	User	9	\N	6	7
10	6	User	10	\N	10	11
11	7	User	11	\N	14	15
12	7	User	12	\N	16	17
6	\N	Group	3	\N	9	12
7	\N	Group	4	\N	13	18
\.


--
-- Data for Name: aros_acos; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY aros_acos (id, aro_id, aco_id, _create, _read, _update, _delete) FROM stdin;
34	1	1	1	1	1	1
35	5	1	-1	-1	-1	-1
36	5	28	1	1	1	1
37	5	16	1	1	1	1
38	5	22	1	1	1	1
39	5	8	1	1	1	1
40	5	38	1	1	1	1
41	5	42	1	1	1	1
42	5	46	1	1	1	1
43	6	1	-1	-1	-1	-1
44	6	16	1	1	1	1
45	6	8	1	1	1	1
46	6	38	1	1	1	1
47	6	42	1	1	1	1
48	6	46	1	1	1	1
49	7	1	-1	-1	-1	-1
50	7	17	1	1	1	1
51	7	18	1	1	1	1
52	7	8	1	1	1	1
53	7	50	-1	-1	-1	-1
54	7	49	-1	-1	-1	-1
55	7	39	1	1	1	1
56	7	52	1	1	1	1
57	7	46	1	1	1	1
\.


--
-- Data for Name: groups; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY groups (id, name, created, modified, specific_program_access) FROM stdin;
1	administrator	2012-01-30 20:48:19	2012-01-30 20:48:19	f
2	manager	2012-01-30 20:49:52	2012-01-30 20:49:52	f
3	program manager	2012-01-30 20:50:00	2012-01-31 08:03:07	t
4	customer	2012-01-30 20:50:08	2012-01-31 08:03:18	t
\.


--
-- Data for Name: programs; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY programs (id, name, country, url, database, created, modified) FROM stdin;
4f26a3cd-8408-4c42-a98a-0ad83745968f	m4h	uganda	m4h	m4h	2012-01-30 15:06:05	2012-01-30 15:06:05
4f26a450-f4f4-44fa-b391-0a123745968f	Mother Reminder System	congo	mrs	mrs	2012-01-30 15:08:16	2012-01-30 15:08:16
4f337849-65d8-4849-9038-11963745968f	wikipedia	kenya	wiki	wiki	2012-02-09 07:39:53	2012-02-09 07:39:53
\.


--
-- Data for Name: programs_users; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY programs_users (id, program_id, user_id) FROM stdin;
3	4f26a3cd-8408-4c42-a98a-0ad83745968f	10
4	4f26a3cd-8408-4c42-a98a-0ad83745968f	11
5	4f26a450-f4f4-44fa-b391-0a123745968f	12
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: cake
--

COPY users (id, username, password, group_id, created, modified) FROM stdin;
8	marcus	e8d58c12a82e4471319b6fb5ec8610807d6cda98	1	2012-01-30 20:56:54	2012-01-30 20:56:54
9	jan	eaf35d49b7fe974eca4ef4b8a8c775f7a8b7d270	2	2012-01-30 20:57:17	2012-01-30 20:57:17
10	maureen	c2260807724f3796957651b60b5bd99eaba9c3ec	3	2012-01-30 20:57:40	2012-01-30 20:57:40
11	unicef	edcd5da41fb73b732af57a5c810ea7735fef646f	4	2012-01-30 20:58:11	2012-01-30 20:58:11
12	unilever	5fa3c44a0dbeb76daafe1bbb62d1954c4d556621	4	2012-01-30 20:58:38	2012-01-30 20:58:38
\.

