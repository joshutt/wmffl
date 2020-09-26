class Injury:
    def __init__(self, player, status):
        self.id = player
        self.status = status
        self.details = None
        self.expectedReturn = None

    @staticmethod
    def load_by_dict(inj_dict):
        injury = Injury(inj_dict['id'], inj_dict['status'])
        injury.set_return(inj_dict['exp_return'])
        injury.set_details(inj_dict['details'])
        return injury

    def set_details(self, details):
        self.details = details

    def set_return(self, exp_return):
        self.expectedReturn = exp_return
