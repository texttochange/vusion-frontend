--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: acos; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE acos (
    id integer NOT NULL,
    parent_id integer,
    model character varying(255) DEFAULT NULL::character varying,
    foreign_key integer,
    alias character varying(255) DEFAULT NULL::character varying,
    lft integer,
    rght integer
);


ALTER TABLE public.acos OWNER TO cake;

--
-- Name: acos_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE acos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.acos_id_seq OWNER TO cake;

--
-- Name: acos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE acos_id_seq OWNED BY acos.id;


--
-- Name: acos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('acos_id_seq', 56, true);


--
-- Name: aros; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE aros (
    id integer NOT NULL,
    parent_id integer,
    model character varying(255) DEFAULT NULL::character varying,
    foreign_key integer,
    alias character varying(255) DEFAULT NULL::character varying,
    lft integer,
    rght integer
);


ALTER TABLE public.aros OWNER TO cake;

--
-- Name: aros_acos; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE aros_acos (
    id integer NOT NULL,
    aro_id integer NOT NULL,
    aco_id integer NOT NULL,
    _create character varying(2) DEFAULT '0'::character varying NOT NULL,
    _read character varying(2) DEFAULT '0'::character varying NOT NULL,
    _update character varying(2) DEFAULT '0'::character varying NOT NULL,
    _delete character varying(2) DEFAULT '0'::character varying NOT NULL
);


ALTER TABLE public.aros_acos OWNER TO cake;

--
-- Name: aros_acos_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE aros_acos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.aros_acos_id_seq OWNER TO cake;

--
-- Name: aros_acos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE aros_acos_id_seq OWNED BY aros_acos.id;


--
-- Name: aros_acos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('aros_acos_id_seq', 57, true);


--
-- Name: aros_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE aros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.aros_id_seq OWNER TO cake;

--
-- Name: aros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE aros_id_seq OWNED BY aros.id;


--
-- Name: aros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('aros_id_seq', 12, true);


--
-- Name: groups; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE groups (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    created timestamp without time zone,
    modified timestamp without time zone,
    specific_program_access boolean
);


ALTER TABLE public.groups OWNER TO cake;

--
-- Name: groups_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.groups_id_seq OWNER TO cake;

--
-- Name: groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE groups_id_seq OWNED BY groups.id;


--
-- Name: groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('groups_id_seq', 4, true);


--
-- Name: programs; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE programs (
    id character varying(36) NOT NULL,
    name character varying(50),
    country character varying(50),
    url character varying(50),
    database character varying(50),
    created timestamp without time zone,
    modified timestamp without time zone,
    timezone character varying(40) DEFAULT 'UTC'::character varying NOT NULL
);


ALTER TABLE public.programs OWNER TO cake;

--
-- Name: programs_users; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE programs_users (
    id integer NOT NULL,
    program_id character varying(36) NOT NULL,
    user_id integer NOT NULL
);


ALTER TABLE public.programs_users OWNER TO cake;

--
-- Name: programs_users_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE programs_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.programs_users_id_seq OWNER TO cake;

--
-- Name: programs_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE programs_users_id_seq OWNED BY programs_users.id;


--
-- Name: programs_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('programs_users_id_seq', 5, true);


--
-- Name: users; Type: TABLE; Schema: public; Owner: cake; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    password character(40) NOT NULL,
    group_id integer NOT NULL,
    created timestamp without time zone,
    modified timestamp without time zone
);


ALTER TABLE public.users OWNER TO cake;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: cake
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO cake;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: cake
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cake
--

SELECT pg_catalog.setval('users_id_seq', 12, true);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE acos ALTER COLUMN id SET DEFAULT nextval('acos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE aros ALTER COLUMN id SET DEFAULT nextval('aros_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE aros_acos ALTER COLUMN id SET DEFAULT nextval('aros_acos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE groups ALTER COLUMN id SET DEFAULT nextval('groups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE programs_users ALTER COLUMN id SET DEFAULT nextval('programs_users_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: cake
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Data for Name: acos; Type: TABLE DATA; Schema: public; Owner: cake
--

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
11	10	\N	\N	index	19	20
35	28	\N	\N	logout	67	68
12	10	\N	\N	view	21	22
13	10	\N	\N	add	23	24
14	10	\N	\N	edit	25	26
10	1	\N	\N	ProgramDocuments	18	29
15	10	\N	\N	delete	27	28
17	16	\N	\N	index	31	32
18	16	\N	\N	view	33	34
19	16	\N	\N	add	35	36
52	38	\N	\N	view	85	86
20	16	\N	\N	edit	37	38
16	1	\N	\N	Programs	30	41
21	16	\N	\N	delete	39	40
23	22	\N	\N	index	43	44
24	22	\N	\N	view	45	46
25	22	\N	\N	add	47	48
26	22	\N	\N	edit	49	50
22	1	\N	\N	ProgramsUsers	42	53
27	22	\N	\N	delete	51	52
29	28	\N	\N	index	55	56
30	28	\N	\N	view	57	58
28	1	\N	\N	Users	54	71
36	1	\N	\N	AclExtras	72	73
37	1	\N	\N	Mongodb	74	75
39	38	\N	\N	index	77	78
44	28	\N	\N	initDB	69	70
49	38	\N	\N	add	79	80
38	1	\N	\N	Participants	76	89
1	\N	\N	\N	controllers	1	106
43	42	\N	\N	index	91	92
45	42	\N	\N	add	93	94
53	42	\N	\N	draft	95	96
54	42	\N	\N	active	97	98
42	1	\N	\N	Scripts	90	101
47	46	\N	\N	index	103	104
55	42	\N	\N	activateDraft	99	100
50	38	\N	\N	edit	81	82
46	1	\N	\N	Status	102	105
56	38	\N	\N	import	87	88
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

COPY programs (id, name, country, url, database, created, modified, timezone) FROM stdin;
4f26a450-f4f4-44fa-b391-0a123745968f	Mother Reminder System	congo	mrs	mrs	2012-01-30 15:08:16	2012-01-30 15:08:16	UTC
4f26a3cd-8408-4c42-a98a-0ad83745968f	m4h	uganda	m4h	m4h	2012-01-30 15:06:05	2012-02-17 08:56:17	Pacific/Wake
4f337849-65d8-4849-9038-11963745968f	wikipedia	kenya	wiki	wiki	2012-02-09 07:39:53	2012-02-17 09:24:08	Africa/Nairobi
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


--
-- Name: acos_pkey; Type: CONSTRAINT; Schema: public; Owner: cake; Tablespace: 
--

ALTER TABLE ONLY acos
    ADD CONSTRAINT acos_pkey PRIMARY KEY (id);


--
-- Name: aros_acos_pkey; Type: CONSTRAINT; Schema: public; Owner: cake; Tablespace: 
--

ALTER TABLE ONLY aros_acos
    ADD CONSTRAINT aros_acos_pkey PRIMARY KEY (id);


--
-- Name: aros_pkey; Type: CONSTRAINT; Schema: public; Owner: cake; Tablespace: 
--

ALTER TABLE ONLY aros
    ADD CONSTRAINT aros_pkey PRIMARY KEY (id);


--
-- Name: programs_pkey; Type: CONSTRAINT; Schema: public; Owner: cake; Tablespace: 
--

ALTER TABLE ONLY programs
    ADD CONSTRAINT programs_pkey PRIMARY KEY (id);


--
-- Name: users_username_key; Type: CONSTRAINT; Schema: public; Owner: cake; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: aro_aco_key; Type: INDEX; Schema: public; Owner: cake; Tablespace: 
--

CREATE UNIQUE INDEX aro_aco_key ON aros_acos USING btree (aro_id, aco_id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

