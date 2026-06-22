-- Corregir rol del administrador
UPDATE usuarios SET rol = 'admin' WHERE username = 'admin' OR nombre_completo LIKE '%Administrador%';

-- Asignar rol 'usuario' a los que tengan rol vacío
UPDATE usuarios SET rol = 'usuario' WHERE rol IS NULL OR rol = '';
