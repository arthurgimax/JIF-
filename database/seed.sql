-- =====================================================================
-- JIF+ — Carga Inicial de Dados de Demonstração (Seed Realista)
-- Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO)
-- Compatível com InfinityFree e Provedores de Hospedagem Compartilhada
-- =====================================================================

-- 1. USUÁRIOS DO SISTEMA (Senhas com hash bcrypt robusto)
-- Contas técnicas de demonstração são criadas inativas; cadastre um administrador próprio.
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `perfil`, `ativo`) VALUES
(1, 'Administrador de Demonstração', 'admin.demo@example.test', '$2y$10$NVVaOG4tXKgS10PkuJsE6uDSDvPEadq6uxSjvvLyMtpwqIVUF0gCO', 'admin', 0),
(2, 'Coordenador de Demonstração', 'coordenador.demo@example.test', '$2y$10$G4tJH2zK2KvlBLr7D/5V6.9JZHWk6b.PEiWM0V0rbeN5WI34sqKE2', 'organizador', 0),
(3, 'Treinador de Demonstração 01', 'treinador01.demo@example.test', '$2y$10$2tKV4PmdqU8R/YgFZsTAROzNXuzpoas0rFU4Z3.Nb7sBcUhn0xeYa', 'treinador', 0),
(4, 'Treinadora de Demonstração 02', 'treinador02.demo@example.test', '$2y$10$oaAvr/CVJq2.ti5ARh9II.tk3IXWqEXfsom74RX8Unse/PIH2g/0i', 'treinador', 0),
(5, 'Atleta de Demonstração 01', 'atleta01.demo@example.test', '$2y$10$bawDX0w7ZoeLoZSSe1a/Kec03PsVxFQW/RX4xydjNeZynblBbuILG', 'atleta', 0);

-- 2. INSTITUIÇÕES E CAMPI PARTICIPANTES
INSERT INTO `instituicoes` (`id`, `nome`, `sigla`, `campus`, `estado`) VALUES
(1, 'Instituto Federal de Rondônia', 'IFRO', 'Porto Velho Calama', 'RO'),
(2, 'Instituto Federal de Rondônia', 'IFRO', 'Ariquemes', 'RO'),
(3, 'Instituto Federal de Rondônia', 'IFRO', 'Ji-Paraná', 'RO'),
(4, 'Instituto Federal de Rondônia', 'IFRO', 'Cacoal', 'RO'),
(5, 'Instituto Federal de Rondônia', 'IFRO', 'Vilhena', 'RO'),
(6, 'Instituto Federal de Rondônia', 'IFRO', 'Guajará-Mirim', 'RO');

-- 3. COMPETIÇÕES ESPORTIVAS
INSERT INTO `competicoes` (`id`, `instituicao_organizadora_id`, `nome`, `edicao`, `ano`, `data_inicio`, `data_fim`, `local_sede`, `status`) VALUES
(1, 1, 'Jogos do Instituto Federal de Rondônia — JIFRO', 'Edição Estadual 2026', 2026, '2026-05-15', '2026-05-22', 'Campus Porto Velho Calama', 'em_andamento'),
(2, 1, 'Etapa Regional Norte dos Jogos dos Institutos Federais', 'JIF Norte 2026', 2026, '2026-08-10', '2026-08-18', 'Porto Velho - RO', 'planejada');

-- 4. MODALIDADES ESPORTIVAS
INSERT INTO `modalidades` (`id`, `competicao_id`, `nome`, `categoria`, `tipo`, `status`) VALUES
(1, 1, 'Futsal', 'masculino', 'coletiva', 'ativa'),
(2, 1, 'Futsal', 'feminino', 'coletiva', 'ativa'),
(3, 1, 'Voleibol', 'masculino', 'coletiva', 'ativa'),
(4, 1, 'Voleibol', 'feminino', 'coletiva', 'ativa'),
(5, 1, 'Basquetebol', 'masculino', 'coletiva', 'ativa'),
(6, 1, 'Handebol', 'masculino', 'coletiva', 'ativa'),
(7, 1, 'Xadrez', 'misto', 'individual', 'ativa'),
(8, 1, 'Atletismo 100m', 'masculino', 'individual', 'ativa');

-- 5. EQUIPES REGISTRADAS
INSERT INTO `equipes` (`id`, `instituicao_id`, `competicao_id`, `modalidade_id`, `responsavel_id`, `nome`) VALUES
(1, 1, 1, 1, 3, 'IFRO Calama — Futsal Masc'),
(2, 2, 1, 1, 4, 'IFRO Ariquemes — Futsal Masc'),
(3, 3, 1, 1, 3, 'IFRO Ji-Paraná — Futsal Masc'),
(4, 4, 1, 1, 4, 'IFRO Cacoal — Futsal Masc'),
(5, 1, 1, 3, 3, 'IFRO Calama — Vôlei Masc'),
(6, 5, 1, 3, 4, 'IFRO Vilhena — Vôlei Masc');

-- 6. ATLETAS PARTICIPANTES
INSERT INTO `atletas` (`id`, `usuario_id`, `instituicao_id`, `nome`, `matricula_estudantil`, `data_nascimento`, `foto_url`) VALUES
(1, 5, 1, 'Atleta de Demonstração 01', 'DEMO-0001', '2007-06-14', NULL),
(2, NULL, 1, 'Atleta de Demonstração 02', 'DEMO-0002', '2007-03-21', NULL),
(3, NULL, 1, 'Atleta de Demonstração 03', 'DEMO-0003', '2006-11-05', NULL),
(4, NULL, 1, 'Atleta de Demonstração 04', 'DEMO-0004', '2007-09-18', NULL),
(5, NULL, 1, 'Atleta de Demonstração 05', 'DEMO-0005', '2008-01-30', NULL),
(6, NULL, 2, 'Atleta de Demonstração 06', 'DEMO-0006', '2007-05-12', NULL),
(7, NULL, 2, 'Atleta de Demonstração 07', 'DEMO-0007', '2006-12-09', NULL),
(8, NULL, 2, 'Atleta de Demonstração 08', 'DEMO-0008', '2007-08-22', NULL),
(9, NULL, 3, 'Atleta de Demonstração 09', 'DEMO-0009', '2007-02-17', NULL),
(10, NULL, 4, 'Atleta de Demonstração 10', 'DEMO-0010', '2008-04-10', NULL);

-- 7. VÍNCULO EQUIPE - ATLETAS (ESCALAÇÃO OFICIAL)
INSERT INTO `equipe_atletas` (`id`, `equipe_id`, `atleta_id`, `numero_camisa`, `posicao`, `status_inscricao`) VALUES
(1, 1, 1, 10, 'Pivô / Ala', 'regular'),
(2, 1, 2, 7, 'Ala Direito', 'regular'),
(3, 1, 3, 1, 'Goleiro', 'regular'),
(4, 1, 4, 4, 'Fixo', 'regular'),
(5, 1, 5, 11, 'Ala Esquerdo', 'regular'),
(6, 2, 6, 9, 'Pivô', 'regular'),
(7, 2, 7, 10, 'Ala', 'regular'),
(8, 2, 8, 1, 'Goleiro', 'regular'),
(9, 3, 9, 8, 'Fixo', 'regular'),
(10, 4, 10, 5, 'Ala', 'regular');

-- 8. PARTIDAS (Jogos realizados e futuros agendados)
INSERT INTO `partidas` (`id`, `competicao_id`, `modalidade_id`, `equipe_a_id`, `equipe_b_id`, `fase_rodada`, `data_hora`, `local_quadra`, `placar_a`, `placar_b`, `situacao`) VALUES
(1, 1, 1, 1, 2, 'Fase de Grupos - Rodada 1', '2026-05-16 09:00:00', 'Ginásio Poliesportivo Calama - Quadra Principal', 5, 2, 'encerrada'),
(2, 1, 1, 3, 4, 'Fase de Grupos - Rodada 1', '2026-05-16 10:30:00', 'Ginásio Poliesportivo Calama - Quadra Principal', 3, 3, 'encerrada'),
(3, 1, 1, 1, 3, 'Fase de Grupos - Rodada 2', '2026-05-17 14:00:00', 'Ginásio Poliesportivo Calama - Quadra Principal', 4, 1, 'encerrada'),
(4, 1, 1, 2, 4, 'Fase de Grupos - Rodada 2', '2026-05-17 15:30:00', 'Ginásio Poliesportivo Calama - Quadra Principal', 2, 2, 'encerrada'),
(5, 1, 1, 1, 4, 'Fase de Grupos - Rodada 3', '2026-05-18 16:00:00', 'Ginásio Poliesportivo Calama - Quadra Principal', NULL, NULL, 'agendada'),
(6, 1, 1, 2, 3, 'Fase de Grupos - Rodada 3', '2026-05-18 17:30:00', 'Ginásio Poliesportivo Calama - Quadra Principal', NULL, NULL, 'agendada'),
(7, 1, 3, 5, 6, 'Final Estadual - Vôlei', '2026-05-19 10:00:00', 'Ginásio do Calama - Quadra 2', 3, 1, 'encerrada');

-- 9. ESTATÍSTICAS INDIVIDUAIS DOS ATLETAS (Gols, Cartões e Faltas)
INSERT INTO `estatisticas_atletas` (`id`, `partida_id`, `atleta_id`, `equipe_id`, `tipo_estatistica`, `valor`) VALUES
-- Partida 1: Calama 5 x 2 Ariquemes
(1, 1, 1, 1, 'gol', 3),            -- Atleta de Demonstração 01 (hat-trick no futsal)
(2, 1, 2, 1, 'gol', 1),            -- Atleta de Demonstração 02
(3, 1, 5, 1, 'gol', 1),            -- Atleta de Demonstração 05
(4, 1, 6, 2, 'gol', 2),            -- Atleta de Demonstração 06
(5, 1, 4, 1, 'cartao_amarelo', 1), -- Atleta de Demonstração 04
(6, 1, 7, 2, 'cartao_amarelo', 1), -- Atleta de Demonstração 07
(7, 1, 1, 1, 'falta', 2),
-- Partida 3: Calama 4 x 1 Ji-Paraná
(8, 3, 1, 1, 'gol', 2),            -- Atleta de Demonstração 01 (+ 2 gols)
(9, 3, 2, 1, 'gol', 2),            -- Atleta de Demonstração 02
(10, 3, 9, 3, 'gol', 1),           -- Atleta de Demonstração 09
(11, 3, 4, 1, 'cartao_amarelo', 1);

-- 10. ESTATÍSTICAS COLETIVAS DAS EQUIPES
INSERT INTO `estatisticas_equipes` (`id`, `partida_id`, `equipe_id`, `tipo_estatistica`, `valor`) VALUES
(1, 1, 1, 'pontos_total', 5),
(2, 1, 2, 'pontos_total', 2),
(3, 3, 1, 'pontos_total', 4),
(4, 3, 3, 'pontos_total', 1),
(5, 7, 5, 'sets_vencidos', 3),
(6, 7, 6, 'sets_vencidos', 1);