<?php $definitions = \Modules\Pe\Config\InpatientRespondent::OPTIONS; ?>
<div class="mb-7">
    <label for="prem-respondent-name" class="form-label required">Nama Responden</label>
    <input id="prem-respondent-name" name="respondent_name" type="text" class="form-control form-control-solid" maxlength="200" autocomplete="name" value="<?= esc($context['respondent_name'] ?? '', 'attr') ?>" placeholder="Masukkan nama responden" required>
</div>
<?php foreach ($definitions as $field => $definition): ?>
    <?php if ($field === 'ward_class' && \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code'] ?? '') === 'IGD') continue; ?>
    <?php if ($field === 'ward_class' && \Modules\Pe\Config\GeneralQuestionnaires::service($instrument['code'] ?? '') === 'RJ'): ?>
        <div class="mb-7">
            <label for="prem-clinic" class="form-label required">Poli</label>
            <select id="prem-clinic" name="clinic" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih poli" required>
                <option value="">Pilih poli</option>
                <?php foreach (\Modules\Pe\Config\InpatientRespondent::CLINICS as $clinic): ?>
                    <option value="<?= esc($clinic, 'attr') ?>"<?= ($context['clinic'] ?? '') === $clinic ? ' selected' : '' ?>><?= esc($clinic) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php continue; ?>
    <?php endif; ?>
    <fieldset class="border-0 p-0 mb-7">
        <legend class="float-none fs-6 fw-bold required"><?= esc($definition['label']) ?><?= $field === 'ward_class' ? ' :' : '' ?></legend>
        <div class="prem-service-choices prem-respondent-choices<?= $field === 'respondent_gender' ? ' prem-respondent-gender' : '' ?>">
            <?php foreach ($definition['choices'] as $value => $label): ?>
                <label class="prem-choice"><input type="radio" name="<?= $field ?>" value="<?= $value ?>" required<?= ($context[$field] ?? '') === $value ? ' checked' : '' ?>><span class="prem-choice-body"><span><?= esc($label) ?></span><i class="bi bi-check-circle-fill"></i></span></label>
            <?php endforeach; ?>
        </div>
        <?php if ($field === 'occupation'): ?>
            <div id="prem-occupation-other-wrap" class="mt-4<?= ($context['occupation'] ?? '') !== 'LAINNYA' ? ' d-none' : '' ?>">
                <label for="prem-occupation-other" class="form-label required">Pekerjaan lainnya</label>
                <input id="prem-occupation-other" name="occupation_other" type="text" maxlength="150" class="form-control form-control-solid" placeholder="Sebutkan pekerjaan lainnya" value="<?= esc($context['occupation_other'] ?? '', 'attr') ?>"<?= ($context['occupation'] ?? '') === 'LAINNYA' ? ' required' : '' ?>>
            </div>
        <?php endif; ?>
    </fieldset>
    <?php if ($field === 'respondent_gender'): ?>
        <div class="mb-7"><label for="prem-respondent-age" class="form-label required">Usia (tahun)</label><input id="prem-respondent-age" name="respondent_age" type="number" min="0" max="120" step="1" inputmode="numeric" class="form-control form-control-solid" placeholder="Masukkan usia dalam tahun" value="<?= esc($context['respondent_age'] ?? '', 'attr') ?>" required></div>
    <?php endif; ?>
<?php endforeach; ?>