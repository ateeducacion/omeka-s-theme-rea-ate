# Protocolo de registro de decisiones

## Propósito
Define el formato exacto, el nivel de detalle y las reglas de cuándo registrar una decisión en los diarios de `decisions/`, para que todos los agentes mantengan un historial coherente y trazable sin burocracia innecesaria.

## Cuándo invocar esta skill
- Al ir a escribir una entrada en cualquier `decisions/*.md`.
- Al necesitar referenciar una decisión previa desde otra entrada.
- Al revisar si una decisión existente debe actualizarse o complementarse.
- Al decidir si algo merece ser decisión o es implementación trivial.

## Referencia técnica

### Formato exacto de una entrada

```markdown
## [YYYY-MM-DD] Título corto de la decisión

**Contexto:** Por qué fue necesario tomar esta decisión. Qué problema resuelve o qué ambigüedad despeja. Máximo 3-5 frases.

**Decisión:** Qué se decidió exactamente. Descripción concreta, sin ambigüedad. Incluir rutas de archivo, nombres de propiedad o valores específicos cuando sean relevantes.

**Alternativas descartadas:** Qué otras opciones se consideraron y por qué no se eligieron. Si no hubo alternativas reales, indicar "N/A — decisión sin ambigüedad".

**Consecuencias:** Qué implica esta decisión para otros agentes, para otros archivos o para el ciclo actual. Qué queda bloqueado, qué queda desbloqueado.

**Agente:** [architect | designer | developer | qa | orchestrator]
```

### Ejemplos de entradas correctas

**Decisión de arquitectura:**
```markdown
## [2026-05-05] Fuente de datos del filtro de colecciones

**Contexto:** La implementación de `item-set/browse` leía metadatos de los ítems hijos para poblar los filtros, lo que requería N+1 queries. QA-001 expuso que los datos reales están en los propios item sets.

**Decisión:** Los filtros de colecciones leen metadatos del propio item set, usando `lrmi:educationalAlignment` para Etapa y `lrmi:educationalLevel` para Nivel. Los datos de los ítems hijos no se agregan en runtime.

**Alternativas descartadas:** Agregación desde ítems hijos — descartada por coste de N queries en render; caché de metadatos agregados — descartada por complejidad de invalidación.

**Consecuencias:** El equipo de contenidos debe informar `lrmi:educationalAlignment` y `lrmi:educationalLevel` directamente en cada item set para que los filtros funcionen. Desbloquea corrección de QA-001.

**Agente:** architect
```

**Decisión de diseño:**
```markdown
## [2026-05-05] Componente unificado para lrmi:learningResourceType

**Contexto:** QA-002 detectó que el badge de tipo de recurso era visualmente distinto en search results y en item show, generando incoherencia percibida.

**Decisión:** Un único partial `common/resource-type-badge` renderiza el badge con icono contextual (Material Symbols), label normalizada y clase `.resource-type-badge`. Ambas vistas usan este partial o replica su lógica en JS.

**Alternativas descartadas:** Dos variantes distintas del badge — descartado porque aumenta divergencia futura.

**Consecuencias:** El developer debe eliminar `.lrt-badge` y unificar en `.resource-type-badge`. El CSS de `.lrt-badge` puede eliminarse.

**Agente:** designer
```

### Cuándo registrar

**SÍ registrar:**
- Decisiones que afectan a más de un archivo del tema.
- Elecciones de tecnología o librería (aunque parezcan obvias).
- Decisiones que descarten una alternativa no trivial.
- Decisiones que desbloqueen o bloqueen trabajo de otro agente.
- Correcciones de bugs que revelen un problema de diseño subyacente.
- Validaciones del orquestador sobre propuestas de otros agentes.

**NO registrar:**
- Correcciones de typos o formateo de código.
- Añadir un `aria-label` a un botón existente.
- Cambiar un valor de padding en SASS sin implicaciones de sistema.
- Añadir un comentario en el código.
- Implementación mecánica sin decisión (seguir exactamente lo que dice otra decisión previa).

### Cómo referenciar una decisión previa

```markdown
Según la decisión del Arquitecto [2026-05-05], los filtros leen desde el item set.

Ver `decisions/architect.md` — entrada [2026-05-05] "Fuente de datos del filtro".
```

No existe un sistema de anclas automáticas en Markdown; la referencia por fecha + título es suficiente.

### Actualizar o complementar una decisión existente

Si una decisión previa queda parcialmente obsoleta o hay un matiz importante:

```markdown
## [2026-05-10] Ampliación de la decisión de filtros de colecciones [2026-05-05]

**Contexto:** La instancia real ha confirmado que `lrmi:educationalAlignment` no siempre está informado. Se necesita un fallback.

**Decisión:** Añadir `dcterms:educationLevel` como fallback de `lrmi:educationalAlignment` para Etapa.

**Agente:** architect
```

No se editan entradas pasadas; se añaden nuevas con referencia a la anterior.

## Errores comunes
- Registrar la implementación como decisión ("Se implementó X en el archivo Y") — las decisiones explican el porqué, no el qué. El qué está en el código y en el commit.
- Entradas sin "Alternativas descartadas" cuando sí las hubo — es la información más valiosa del diario; sin ella, la próxima persona repetirá el mismo análisis.
- Agente incorrecto en la entrada — si el desarrollador toma una decisión de diseño sin consultar al diseñador, registrarla como `developer` y notificar al diseñador para validación.
- Fechas en formato no ISO — usar siempre `YYYY-MM-DD` para que las entradas sean ordenables y comparables.
