-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS site_empresa 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE site_empresa;

-- Tabela de usuários administrativos
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(32) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    nivel ENUM('admin', 'editor') DEFAULT 'editor',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações do site
CREATE TABLE config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT,
    descricao VARCHAR(255),
    tipo ENUM('texto', 'imagem', 'senha', 'numero') DEFAULT 'texto',
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de menus da navegação
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    texto LONGTEXT,
    imagem VARCHAR(255),
    icone VARCHAR(50),
    ordem INT DEFAULT 0,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    tipo_menu ENUM('topo', 'rodape', 'lateral') DEFAULT 'topo',
    target ENUM('_self', '_blank') DEFAULT '_self',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_ordem (ordem),
    INDEX idx_tipo_menu (tipo_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de contatos recebidos do formulário
CREATE TABLE contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(200),
    mensagem TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    origem VARCHAR(50) DEFAULT 'site',
    status ENUM('novo', 'lido', 'respondido', 'arquivado') DEFAULT 'novo',
    respondido_por INT,
    resposta TEXT,
    data_resposta TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_data_criacao (data_criacao),
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para logs do sistema
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    modulo VARCHAR(50),
    descricao TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_acao (acao),
    INDEX idx_data (data_registro),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para seções adicionais da página inicial
CREATE TABLE secoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    subtitulo VARCHAR(500),
    conteudo LONGTEXT,
    imagem VARCHAR(255),
    icone VARCHAR(50),
    ordem INT DEFAULT 0,
    tipo VARCHAR(50) DEFAULT 'secao',
    classe_css VARCHAR(100),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_ordem (ordem),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para configurações de SEO
CREATE TABLE seo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagina VARCHAR(100) NOT NULL UNIQUE,
    titulo VARCHAR(200),
    descricao TEXT,
    palavras_chave TEXT,
    og_image VARCHAR(255),
    og_title VARCHAR(200),
    og_description TEXT,
    twitter_card VARCHAR(50),
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados iniciais

-- Usuários administrativos
INSERT INTO usuarios (usuario, senha, nome, nivel) VALUES 
('ttatim', MD5('123456'), 'Ttatim', 'admin'),
('telma', MD5('123456'), 'Telma', 'admin');

-- Configurações padrão do site
INSERT INTO config (chave, valor, descricao, tipo) VALUES 
('logo', '', 'Logo do site (aparece no cabeçalho)', 'imagem'),
('logo_footer', '', 'Logo do rodapé', 'imagem'),
('favicon', '', 'Ícone da aba do navegador', 'imagem'),
('recaptcha_site_key', '', 'Chave pública do Google reCAPTCHA', 'texto'),
('recaptcha_secret_key', '', 'Chave secreta do Google reCAPTCHA', 'texto'),
('email_contato', 'contato@empresa.com', 'E-mail para receber contatos', 'texto'),
('telefone_contato', '(11) 99999-9999', 'Telefone de contato', 'texto'),
('endereco', 'Rua Exemplo, 123 - São Paulo/SP', 'Endereço da empresa', 'texto'),
('titulo_site', 'Minha Empresa', 'Título do site', 'texto'),
('descricao_site', 'Descrição da empresa e serviços oferecidos', 'texto'),
('cor_primaria', '#4169E1', 'Cor primária (Royal Blue)', 'texto'),
('cor_secundaria', '#F8F8FF', 'Cor secundária (Ghost White)', 'texto'),
('facebook_url', 'https://facebook.com/empresa', 'Link do Facebook', 'texto'),
('instagram_url', 'https://instagram.com/empresa', 'Link do Instagram', 'texto'),
('linkedin_url', 'https://linkedin.com/company/empresa', 'Link do LinkedIn', 'texto'),
('whatsapp_numero', '5511999999999', 'Número do WhatsApp com código do país', 'texto'),
('whatsapp_mensagem', 'Olá! Gostaria de mais informações.', 'Mensagem padrão do WhatsApp', 'texto'),
('manutencao', '0', 'Modo manutenção (0=off, 1=on)', 'numero'),
('texto_manutencao', 'Site em manutenção. Volte em breve!', 'Texto do modo manutenção', 'texto');

-- Menus padrão
INSERT INTO menus (titulo, slug, texto, ordem, status, tipo_menu) VALUES 
('Home', 'home', 'Página inicial com todas as seções', 1, 'ativo', 'topo'),
('Sobre', 'sobre', 'Conheça mais sobre nossa empresa e história', 2, 'ativo', 'topo'),
('Serviços', 'servicos', 'Conheça nossos serviços e soluções', 3, 'ativo', 'topo'),
('Portfólio', 'portfolio', 'Veja alguns de nossos trabalhos', 4, 'ativo', 'topo'),
('Contato', 'contato', 'Entre em contato conosco', 5, 'ativo', 'topo'),
('Política de Privacidade', 'politica-privacidade', 'Nossa política de privacidade', 1, 'ativo', 'rodape'),
('Termos de Uso', 'termos-uso', 'Termos de uso do site', 2, 'ativo', 'rodape');

-- Seções da página inicial
INSERT INTO secoes (titulo, subtitulo, conteudo, ordem, tipo, status) VALUES 
('Bem-vindo à Nossa Empresa', 'Soluções inovadoras para o seu negócio', '<p>Somos uma empresa dedicada a fornecer as melhores soluções para nossos clientes. Com anos de experiência no mercado, entregamos qualidade e inovação em todos os nossos projetos.</p><p>Nossa equipe é composta por profissionais altamente qualificados, prontos para atender suas necessidades específicas e ajudar seu negócio a crescer.</p>', 1, 'hero', 'ativo'),
('Nossos Serviços', 'Conheça o que oferecemos', '<div class="servicos-grid"><div class="servico"><h3>Consultoria</h3><p>Consultoria especializada para alavancar seus resultados.</p></div><div class="servico"><h3>Desenvolvimento</h3><p>Soluções tecnológicas personalizadas para sua empresa.</p></div><div class="servico"><h3>Suporte</h3><p>Suporte técnico especializado e ágil.</p></div></div>', 2, 'servicos', 'ativo'),
('Por que nos escolher?', 'Veja nossas vantagens competitivas', '<ul><li>Experiência de mais de 10 anos no mercado</li><li>Equipe especializada e certificada</li><li>Atendimento personalizado</li><li>Soluções inovadoras e customizadas</li><li>Suporte pós-implantação</li><li>Preços competitivos</li></ul>', 3, 'vantagens', 'ativo');

-- Configurações de SEO
INSERT INTO seo (pagina, titulo, descricao, palavras_chave) VALUES 
('home', 'Minha Empresa - Soluções Inovadoras', 'Oferecemos as melhores soluções para seu negócio. Qualidade, inovação e resultados comprovados.', 'empresa, soluções, negócio, inovação, qualidade'),
('contato', 'Entre em Contato - Minha Empresa', 'Entre em contato conosco para mais informações sobre nossos serviços e soluções.', 'contato, orçamento, informações, suporte'),
('sobre', 'Sobre Nós - Minha Empresa', 'Conheça nossa história, missão, visão e valores. Saiba por que somos a melhor escolha.', 'sobre nós, história, missão, valores, empresa');

-- Views para facilitar consultas

-- View para menus ativos ordenados
CREATE VIEW view_menus_ativos AS
SELECT id, titulo, slug, imagem, icone, ordem, target, tipo_menu
FROM menus 
WHERE status = 'ativo'
ORDER BY tipo_menu, ordem, titulo;

-- View para configurações formatadas
CREATE VIEW view_config_formatada AS
SELECT 
    chave,
    valor,
    descricao,
    tipo,
    CASE tipo
        WHEN 'imagem' THEN CONCAT('/uploads/', valor)
        ELSE valor
    END as valor_formatado
FROM config;

-- View para contatos recentes
CREATE VIEW view_contatos_recentes AS
SELECT 
    c.id,
    c.nome,
    c.email,
    c.assunto,
    LEFT(c.mensagem, 100) as mensagem_preview,
    c.status,
    DATE_FORMAT(c.data_criacao, '%d/%m/%Y %H:%i') as data_formatada,
    u.nome as respondido_por_nome
FROM contatos c
LEFT JOIN usuarios u ON c.respondido_por = u.id
ORDER BY c.data_criacao DESC;

-- Stored Procedures

-- Procedure para registrar log
DELIMITER $$
CREATE PROCEDURE registrar_log(
    IN p_usuario_id INT,
    IN p_acao VARCHAR(100),
    IN p_modulo VARCHAR(50),
    IN p_descricao TEXT,
    IN p_ip VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    INSERT INTO logs (usuario_id, acao, modulo, descricao, ip_address, user_agent)
    VALUES (p_usuario_id, p_acao, p_modulo, p_descricao, p_ip, p_user_agent);
END$$
DELIMITER ;

-- Procedure para atualizar último login
DELIMITER $$
CREATE PROCEDURE atualizar_ultimo_login(
    IN p_usuario_id INT
)
BEGIN
    UPDATE usuarios 
    SET ultimo_login = CURRENT_TIMESTAMP 
    WHERE id = p_usuario_id;
END$$
DELIMITER ;

-- Triggers

-- Trigger para criar slug automaticamente
DELIMITER $$
CREATE TRIGGER before_insert_menus
BEFORE INSERT ON menus
FOR EACH ROW
BEGIN
    IF NEW.slug IS NULL OR NEW.slug = '' THEN
        SET NEW.slug = LOWER(REPLACE(NEW.titulo, ' ', '-'));
        SET NEW.slug = REGEXP_REPLACE(NEW.slug, '[^a-z0-9-]', '');
    END IF;
END$$
DELIMITER ;

-- Trigger para logs automáticos de alterações em menus
DELIMITER $$
CREATE TRIGGER after_update_menus
AFTER UPDATE ON menus
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        CALL registrar_log(
            NULL, 
            'ALTERAÇÃO_STATUS_MENU', 
            'menus', 
            CONCAT('Menu "', OLD.titulo, '" alterado de ', OLD.status, ' para ', NEW.status),
            NULL, 
            NULL
        );
    END IF;
END$$
DELIMITER ;

-- Índices adicionais para otimização
CREATE INDEX idx_config_chave ON config(chave);
CREATE INDEX idx_menus_slug ON menus(slug);
CREATE INDEX idx_contatos_data ON contatos(data_criacao);
CREATE INDEX idx_secoes_tipo_status ON secoes(tipo, status);

-- Comentários das tabelas
ALTER TABLE usuarios COMMENT = 'Usuários do sistema administrativo';
ALTER TABLE config COMMENT = 'Configurações gerais do site';
ALTER TABLE menus COMMENT = 'Menus de navegação do site';
ALTER TABLE contatos COMMENT = 'Contatos recebidos pelo formulário';
ALTER TABLE logs COMMENT = 'Logs de atividades do sistema';
ALTER TABLE secoes COMMENT = 'Seções da página inicial';
ALTER TABLE seo COMMENT = 'Configurações de SEO por página';